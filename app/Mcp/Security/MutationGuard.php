<?php

declare(strict_types=1);

namespace App\Mcp\Security;

use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class MutationGuard
{
    /**
     * Strict list of Eloquent model classes permitted for MCP operations.
     *
     * @var array<string, class-string<Model>>
     */
    protected array $allowedModels = [
        'page' => Page::class,
        'pagemeta' => PageMeta::class,
        'page_meta' => PageMeta::class,
        'product' => Product::class,
        'category' => ProductCategory::class,
        'product_category' => ProductCategory::class,
        'attribute' => ProductAttribute::class,
        'product_attribute' => ProductAttribute::class,
        'project' => Project::class,
        'enquiry' => Enquiry::class,
        'email_template' => EmailTemplate::class,
    ];

    /**
     * Columns that can never be modified through MCP mutations.
     *
     * @var array<string>
     */
    protected array $forbiddenColumns = [
        'id',
        'password',
        'remember_token',
        'api_token',
        'is_admin',
        'role',
        'email_verified_at',
        'created_at',
        'deleted_at',
    ];

    /**
     * @return array<string, class-string<Model>>
     */
    public function getAllowedModels(): array
    {
        return $this->allowedModels;
    }

    /**
     * Resolve model class and ensure it is allowlisted.
     */
    public function resolveModel(string $modelKey): Model
    {
        $key = strtolower(trim($modelKey));
        if (! isset($this->allowedModels[$key])) {
            $allowed = implode(', ', array_keys($this->allowedModels));
            throw new InvalidArgumentException("Model '{$modelKey}' is not permitted. Allowed models: {$allowed}");
        }

        $class = $this->allowedModels[$key];

        return new $class;
    }

    /**
     * Validate payload attributes against forbidden columns and fillable properties.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function sanitizeAttributes(Model $model, array $attributes): array
    {
        $clean = [];
        $fillable = $model->getFillable();

        foreach ($attributes as $key => $value) {
            if (in_array(strtolower((string) $key), $this->forbiddenColumns, true)) {
                throw new InvalidArgumentException("Modification of protected column '{$key}' is forbidden.");
            }

            if (! empty($fillable) && ! in_array((string) $key, $fillable, true)) {
                throw new InvalidArgumentException("Column '{$key}' is not fillable on model ".get_class($model));
            }

            $clean[(string) $key] = $value;
        }

        return $clean;
    }
}
