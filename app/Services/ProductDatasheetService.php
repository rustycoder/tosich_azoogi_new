<?php

namespace App\Services;

use App\Models\DatasheetTemplate;
use App\Models\Product;
use App\Models\ProductDatasheetExport;
use App\Repositories\Contracts\IProductDatasheetRepository;
use App\Repositories\Contracts\IProductRepository;
use App\Services\Contracts\IEmailTemplateService;
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
        private IEmailTemplateService $emailTemplates,
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

        $export = $this->exports->create([
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

        $this->emailTemplates->sendDatasheetNotification($export);

        return $export;
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

        $specificationsTable = $this->renderSpecificationsTable($specifications);

        return [
            'title' => (string) ($export->product_name ?: $export->product_code),
            'name' => (string) $export->product_name,
            'product_name' => (string) ($export->product_name ?: $export->product_code),
            'product_code' => (string) ($export->product_code ?? ''),
            'category' => (string) ($configuration['category'] ?? ''),
            'description' => (string) ($configuration['description'] ?? ''),
            'specifications' => $specifications,
            'specifications_table' => $specificationsTable,
            'product_image' => $export->listingImageUrl(),
            'dimension_image' => $this->dimensionImageUrl($export, $configuration),
            'project_name' => $export->project_name,
            'person_name' => $export->person_name,
            'reviewed_on' => $export->created_at?->timezone(config('app.timezone'))->format('d/m/Y') ?? now()->format('d/m/Y'),
            'email' => 'sales@azoogi.com',
            'phone' => '1300 641 261',
            'website' => 'www.azoogi.com.au',
            'address' => 'Unit 47, 10-12 Girawah Place, Matraville, NSW, 2036',
            'logo_left' => '/assets/img/datasheet-logo.png',
            'logo_right' => '/assets/img/lighting-council.png',
            'review_logo' => '/assets/logo_dark.png',
            'technical_icons' => $this->renderTechnicalIcons($export, $configuration),
            'specification_icons' => $this->renderTechnicalIcons($export, $configuration),
        ];
    }

    public function renderTechnicalIcons(ProductDatasheetExport $export, array $configuration = []): string
    {
        $product = $export->product;
        $features = is_array($product?->product_features) ? $product->product_features : [];

        $rawIcons = $product?->technical_icons
            ?? ($configuration['technical_icons'] ?? null)
            ?? ($features['Technical Icons'] ?? null)
            ?? ($features['Technical icons'] ?? null)
            ?? ($features['technical_icons'] ?? null)
            ?? ($features['Product Icons'] ?? null)
            ?? ($features['Product icons'] ?? null)
            ?? ($features['product_icons'] ?? null)
            ?? ($features['Icons'] ?? null)
            ?? ($features['Icon'] ?? null);

        $iconUrls = [];
        if (is_array($rawIcons)) {
            foreach ($rawIcons as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $iconUrls[] = trim($item);
                } elseif (is_array($item) && ! empty($item['url'])) {
                    $iconUrls[] = trim((string) $item['url']);
                }
            }
        } elseif (is_string($rawIcons) && trim($rawIcons) !== '') {
            $iconUrls[] = trim($rawIcons);
        }

        $iconUrls = array_values(array_unique($iconUrls));

        if (! empty($iconUrls)) {
            $html = '';
            foreach ($iconUrls as $url) {
                $resolved = htmlspecialchars(media_url($url), ENT_QUOTES, 'UTF-8');
                $html .= '<img class="spec-icon-img" src="'.$resolved.'" alt="Specification Icon">';
            }

            return $html;
        }

        // Fallback: extract specification badges from specs
        $specs = is_array($configuration['specifications'] ?? null) ? $configuration['specifications'] : [];
        $specMap = [];
        foreach ($specs as $s) {
            if (is_array($s) && ! empty($s['label'])) {
                $specMap[strtolower(trim((string) $s['label']))] = trim((string) ($s['value'] ?? ''));
            }
        }

        $badges = [];

        // Warranty badge
        $warranty = $specMap['warranty'] ?? $this->joinValues($this->featureValues($features, 'Warranty', 'warranty'));
        if ($warranty !== '') {
            if (preg_match('/(\d+)\s*year/i', $warranty, $m)) {
                $badges[] = $m[1].'YR';
            } elseif (mb_strlen($warranty) <= 6) {
                $badges[] = strtoupper($warranty);
            }
        }

        // IP Rating badge
        $ip = $specMap['ip rating'] ?? $specMap['ingress'] ?? $this->joinValues($this->featureValues($features, 'IP Rating', 'IP rating', 'Ingress'));
        if ($ip !== '') {
            if (preg_match('/(IP\s*\d{2})/i', $ip, $m)) {
                $badges[] = strtoupper(str_replace(' ', '', $m[1]));
            }
        }

        // Voltage badge
        $voltage = $specMap['input voltage'] ?? $specMap['voltage'] ?? $this->joinValues($this->featureValues($features, 'Input Voltage', 'Voltage'));
        if ($voltage !== '') {
            if (preg_match('/(\d+)\s*V/i', $voltage, $m)) {
                $badges[] = $m[1].'V';
            }
        }

        // Dimming badge
        $dimming = $specMap['dimming'] ?? $specMap['control'] ?? $this->joinValues($this->featureValues($features, 'Dimming', 'Control', 'Dimming / Control'));
        if ($dimming !== '') {
            if (stripos($dimming, 'DALI') !== false) {
                $badges[] = 'DALI';
            } elseif (stripos($dimming, '1-10V') !== false) {
                $badges[] = '1-10V';
            } elseif (stripos($dimming, 'Phase') !== false) {
                $badges[] = 'PHASE';
            } elseif (stripos($dimming, 'Casambi') !== false) {
                $badges[] = 'CASAMBI';
            }
        }

        if (empty($badges)) {
            $badges = ['5YR', 'IP67', '24V', 'DALI'];
        }

        $html = '';
        foreach ($badges as $badge) {
            $html .= '<span class="feature-badge">'.htmlspecialchars($badge, ENT_QUOTES, 'UTF-8').'</span>';
        }

        return $html;
    }

    public function renderSpecificationsTable(array $specifications): string
    {
        if (empty($specifications)) {
            return '';
        }

        $html = '<p class="params-title">SPECIFICATIONS</p>'."\n";
        $html .= '<table class="params">'."\n";
        foreach ($specifications as $row) {
            $label = htmlspecialchars((string) ($row['label'] ?? ''), ENT_QUOTES, 'UTF-8');
            $value = htmlspecialchars((string) ($row['value'] ?? ''), ENT_QUOTES, 'UTF-8');
            $html .= '    <tr>'."\n";
            $html .= '        <td>'.$label.'</td>'."\n";
            $html .= '        <td>'.$value.'</td>'."\n";
            $html .= '    </tr>'."\n";
        }
        $html .= '</table>';

        return $html;
    }

    public function sampleData(): array
    {
        $specifications = [
            ['label' => 'Brand', 'value' => 'Azoogi'],
            ['label' => 'Dimensions', 'value' => '10mm (W) x 10mm (H)'],
            ['label' => 'Light Source', 'value' => 'SMD LED'],
            ['label' => 'Wattage', 'value' => '10W/m'],
            ['label' => 'Luminous Flux', 'value' => '850 lm/m'],
            ['label' => 'Optics', 'value' => '120°'],
            ['label' => 'Color Temperature', 'value' => '3000K Warm White'],
            ['label' => 'IP Rating', 'value' => 'IP67'],
            ['label' => 'Dimming', 'value' => 'DALI-2 / 1-10V / Phase'],
            ['label' => 'Warranty', 'value' => '5 Years'],
        ];

        return [
            'title' => 'Mini Neon Side View',
            'name' => 'Mini Neon Side View',
            'product_name' => 'Mini Neon Side View',
            'product_code' => 'SV1010-10W-3K-IP67',
            'category' => 'Side View Neon Flex',
            'description' => 'Precision architectural linear neon with seamless side-bending optical distribution, UV stabilisation, and IP67 weather resistance for exterior facades and cove details.',
            'specifications' => $specifications,
            'specifications_table' => $this->renderSpecificationsTable($specifications),
            'product_image' => '/assets/img/neon.webp',
            'dimension_image' => '/assets/img/neon.webp',
            'project_name' => '',
            'person_name' => '',
            'reviewed_on' => '',
            'email' => 'sales@azoogi.com',
            'phone' => '1300 641 261',
            'website' => 'www.azoogi.com.au',
            'address' => 'Unit 47, 10-12 Girawah Place, Matraville, NSW, 2036',
            'logo_left' => '/assets/img/datasheet-logo.png',
            'logo_right' => '/assets/img/lighting-council.png',
            'review_logo' => '/assets/logo_dark.png',
            'technical_icons' => '<span class="feature-badge">5YR</span><span class="feature-badge">IP67</span><span class="feature-badge">24V</span><span class="feature-badge">DALI</span>',
            'specification_icons' => '<span class="feature-badge">5YR</span><span class="feature-badge">IP67</span><span class="feature-badge">24V</span><span class="feature-badge">DALI</span>',
        ];
    }

    public function renderTemplate(DatasheetTemplate $template, ?ProductDatasheetExport $export = null, bool $autoPrint = false): string
    {
        $data = $export !== null ? $this->sheet($export) : $this->sampleData();

        return $template->render($data, $autoPrint);
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
        $description = trim((string) ($product->product_description ?? ''));

        return [
            'category' => (string) ($product->category ?? ''),
            'description' => $description,
            'specifications' => $this->specifications($product, $productCode, $selectedOptions, $length),
            'selected_options' => $selectedOptions,
            'length' => $length,
            'product_image' => $this->firstAsset($product->product_images) ?: (string) ($product->cover ?? ''),
            'dimension_image' => $this->firstAsset($product->product_dimension),
            'technical_icons' => $product->technical_icons,
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
