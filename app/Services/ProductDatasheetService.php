<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductDatasheetExport;
use App\Repositories\Contracts\IProductDatasheetRepository;
use App\Repositories\Contracts\IProductRepository;
use App\Services\Contracts\IProductDatasheetService;
use App\Services\Contracts\IVisitorOriginService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductDatasheetService implements IProductDatasheetService
{
    /**
     * @var array<string, list<string>>
     */
    private const SPEC_FIELDS = [
        'Brand' => ['Brand', 'Supplier Name'],
        'Dimensions' => ['Dimension', 'Dimensions', 'Size', 'Strip Width'],
        'Light Source' => ['Light Source', 'LED Type', 'Chip'],
        'Wattage' => ['Wattage', 'Power', 'Power Consumption Rate'],
        'Luminous Flux' => ['Luminous Flux', 'Lumen', 'Lumens'],
        'Optics' => ['Optics', 'Beam Angle', 'Beam'],
        'Color Temperature' => ['Color Temperature', 'Colour Temperature', 'CCT', 'Light Color'],
        'IP Rating' => ['IP Rating', 'IP'],
        'Dimming' => ['Dimming', 'Dimmable', 'Dimming Type', 'Dimming Control', 'Control Option'],
        'Mounting' => ['Mounting', 'Installation', 'Mount'],
        'Warranty' => ['Warranty'],
    ];

    public function __construct(
        private IProductRepository $products,
        private IProductDatasheetRepository $exports,
        private IVisitorOriginService $origin,
    ) {}

    public function export(array $data): ProductDatasheetExport
    {
        $product = $this->products->publishedByAirtableId($data['product_id']);

        if ($product === null) {
            throw new NotFoundHttpException('Product not found.');
        }

        $selectedOptions = $this->stringMap($data['selected_options'] ?? []);
        $productCode = trim((string) ($product->product_code ?? '')) ?: trim((string) ($data['product_code'] ?? ''));
        $length = isset($data['length']) && is_numeric($data['length']) ? (float) $data['length'] : null;
        $origin = $this->origin->capture();

        return $this->exports->create([
            'uuid' => (string) Str::uuid(),
            'product_id' => $product->id,
            'airtable_id' => $product->airtable_id,
            'product_code' => $productCode !== '' ? mb_substr($productCode, 0, 191) : null,
            'product_name' => mb_substr((string) $product->product_name, 0, 191),
            'project_name' => mb_substr(trim($data['project_name']), 0, 191),
            'person_name' => mb_substr(trim($data['person_name']), 0, 191),
            'configuration' => $this->snapshot($product, $productCode, $selectedOptions, $length),
            'ip_address' => $origin['ip_address'],
            'country' => $origin['country'],
            'user_agent' => $origin['user_agent'],
        ]);
    }

    public function sheet(ProductDatasheetExport $export): array
    {
        $export->loadMissing('product');
        $configuration = is_array($export->configuration) ? $export->configuration : [];
        $specifications = [];

        foreach ($configuration['specifications'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));

            if ($label === '' || $value === '') {
                continue;
            }

            $specifications[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return [
            'title' => (string) ($export->product_name ?: $export->product_code),
            'name' => (string) $export->product_name,
            'product_code' => (string) ($export->product_code ?? ''),
            'category' => (string) ($configuration['category'] ?? ''),
            'description' => (string) ($configuration['description'] ?? ''),
            'specifications' => $specifications,
            'product_image' => $export->listingImageUrl(),
            'dimension_image' => $this->dimensionImageUrl($export, $configuration),
            'project_name' => $export->project_name,
            'person_name' => $export->person_name,
            'reviewed_on' => $export->created_at?->timezone(config('app.timezone'))->format('d/m/Y') ?? now()->format('d/m/Y'),
            'email' => 'sales@azoogi.com',
            'phone' => '1300 641 261',
            'website' => 'www.azoogi.com.au',
            'address' => 'Unit 47, 10-12 Girawah Place, Matraville, NSW, 2036',
        ];
    }

    public function dashboardList(string $search = ''): LengthAwarePaginator
    {
        return $this->exports->dashboardList($search);
    }

    /**
     * @param  array<string, string>  $selectedOptions
     * @return array<string, mixed>
     */
    private function snapshot(Product $product, string $productCode, array $selectedOptions, ?float $length): array
    {
        $description = trim((string) ($product->product_short_description ?: $product->product_description));

        return [
            'category' => (string) ($product->category ?? ''),
            'description' => $description,
            'specifications' => $this->specifications($product, $productCode, $selectedOptions, $length),
            'selected_options' => $selectedOptions,
            'length' => $length,
            'product_image' => $this->firstAsset($product->product_images) ?: (string) ($product->cover ?? ''),
            'dimension_image' => $this->firstAsset($product->product_dimension),
        ];
    }

    /**
     * @param  array<string, string>  $selectedOptions
     * @return list<array{label: string, value: string}>
     */
    private function specifications(Product $product, string $productCode, array $selectedOptions, ?float $length): array
    {
        $features = is_array($product->product_features) ? $product->product_features : [];
        $rows = [];
        $usedOptionKeys = [];

        foreach (self::SPEC_FIELDS as $label => $aliases) {
            $value = match ($label) {
                'Brand' => $this->joinValues($this->featureValues($features, ...$aliases)) ?: 'Azoogi',
                default => $this->joinValues($this->featureValues($features, ...$aliases)),
            };

            foreach ($selectedOptions as $optionKey => $optionValue) {
                if ($this->optionMatches($optionKey, [$label, ...$aliases])) {
                    $value = $optionValue;
                    $usedOptionKeys[] = $optionKey;
                    break;
                }
            }

            if ($value === '') {
                continue;
            }

            $rows[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        $excludedOptionKeys = ['model', 'product code', 'product_code', 'sku'];

        foreach ($selectedOptions as $optionKey => $optionValue) {
            if (in_array($optionKey, $usedOptionKeys, true) || $optionValue === '' || in_array(strtolower(trim($optionKey)), $excludedOptionKeys, true)) {
                continue;
            }

            $rows[] = [
                'label' => $optionKey,
                'value' => $optionValue,
            ];
        }

        return $rows;
    }

    /**
     * @param  list<string>  $aliases
     */
    private function optionMatches(string $optionKey, array $aliases): bool
    {
        $needle = $this->normalizeKey($optionKey);

        foreach ($aliases as $alias) {
            if ($needle === $this->normalizeKey($alias)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeKey(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '', $value) ?? $value);
    }

    /**
     * @param  array<string, mixed>  $features
     * @return list<string>
     */
    private function featureValues(array $features, string ...$keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            if (! array_key_exists($key, $features)) {
                continue;
            }

            foreach ($this->flattenFeature($features[$key]) as $value) {
                $values[] = $value;
            }
        }

        return $values;
    }

    /**
     * @return list<string>
     */
    private function flattenFeature(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value) || is_numeric($value)) {
            return [trim((string) $value)];
        }

        if (! is_array($value)) {
            return [];
        }

        if (isset($value['value'])) {
            return $this->flattenFeature($value['value']);
        }

        $values = [];

        foreach ($value as $item) {
            foreach ($this->flattenFeature($item) as $inner) {
                $values[] = $inner;
            }
        }

        return $values;
    }

    /**
     * @param  list<string>  $values
     */
    private function joinValues(array $values): string
    {
        $unique = [];

        foreach ($values as $value) {
            $trimmed = trim($value);

            if ($trimmed === '' || in_array($trimmed, $unique, true)) {
                continue;
            }

            $unique[] = $trimmed;
        }

        return implode(' / ', $unique);
    }

    /**
     * @param  array<string, mixed>|null  $options
     * @return array<string, string>
     */
    private function stringMap(?array $options): array
    {
        $map = [];

        foreach ($options ?? [] as $key => $value) {
            $label = trim((string) $key);
            $text = trim((string) $value);

            if ($label === '' || $text === '') {
                continue;
            }

            $map[mb_substr($label, 0, 191)] = mb_substr($text, 0, 191);
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $configuration
     */
    private function dimensionImageUrl(ProductDatasheetExport $export, array $configuration): string
    {
        $live = $export->product !== null
            ? media_url($this->firstAsset($export->product->product_dimension))
            : '';

        if ($live !== '') {
            return $live;
        }

        return media_url((string) ($configuration['dimension_image'] ?? ''));
    }

    private function firstAsset(mixed $value): string
    {
        foreach ($this->flattenFeature($value) as $item) {
            if ($item !== '') {
                return $item;
            }
        }

        return '';
    }
}
