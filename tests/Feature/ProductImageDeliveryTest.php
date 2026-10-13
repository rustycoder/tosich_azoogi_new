<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductImageDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $testCacheDir = storage_path('framework/cache/product-images');
        if (File::isDirectory($testCacheDir)) {
            File::deleteDirectory($testCacheDir);
        }

        parent::tearDown();
    }

    private function createPngBinary(int $w = 10, int $h = 10): string
    {
        $im = imagecreatetruecolor($w, $h);
        $red = imagecolorallocate($im, 255, 0, 0);
        imagefill($im, 0, 0, $red);
        ob_start();
        imagepng($im);
        $data = (string) ob_get_clean();
        imagedestroy($im);

        return $data;
    }

    public function test_serves_product_cover_as_jpg_by_slug(): void
    {
        $png = $this->createPngBinary();
        Http::fake([
            'https://cdn.example.com/cover.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);

        $product = Product::factory()->create([
            'slug' => 'test-linear-profile',
            'product_code' => 'PR130-W',
            'cover' => 'https://cdn.example.com/cover.png',
            'status' => 'publish',
        ]);

        $response = $this->get('/products/test-linear-profile.jpg');

        $response->assertOk();
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
        $this->assertEquals('inline', $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->headers->get('ETag'));
        $this->assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function test_serves_product_cover_as_png_by_sku(): void
    {
        $png = $this->createPngBinary();
        Http::fake([
            'https://cdn.example.com/cover.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);

        Product::factory()->create([
            'slug' => 'sample-strip',
            'product_code' => 'ST-001',
            'cover' => 'https://cdn.example.com/cover.png',
            'status' => 'publish',
        ]);

        $response = $this->get('/products/ST-001.png');

        $response->assertOk();
        $this->assertEquals('image/png', $response->headers->get('Content-Type'));
        $this->assertEquals('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_serves_product_cover_as_webp_by_airtable_id(): void
    {
        $png = $this->createPngBinary();
        Http::fake([
            'https://cdn.example.com/cover.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);

        Product::factory()->create([
            'airtable_id' => 'recAirtable123',
            'product_code' => 'AT-SKU',
            'slug' => 'airtable-product',
            'cover' => 'https://cdn.example.com/cover.png',
            'status' => 'publish',
        ]);

        $response = $this->get('/products/recAirtable123.webp');

        $response->assertOk();
        $this->assertEquals('image/webp', $response->headers->get('Content-Type'));
    }

    public function test_serves_product_dimension_image_with_dimension_suffix(): void
    {
        $cadPng = $this->createPngBinary();
        Http::fake([
            'https://cdn.example.com/cad-dim.png' => Http::response($cadPng, 200, ['Content-Type' => 'image/png']),
        ]);

        Product::factory()->create([
            'slug' => 'deep-surfaced-profile-171x135mm',
            'product_code' => 'PR006',
            'product_dimension' => ['https://cdn.example.com/cad-dim.png'],
            'status' => 'publish',
        ]);

        // 1. By slug
        $response1 = $this->get('/products/deep-surfaced-profile-171x135mm-dimension.jpg');
        $response1->assertOk();
        $this->assertEquals('image/jpeg', $response1->headers->get('Content-Type'));

        // 2. By SKU / product code
        $response2 = $this->get('/products/PR006-dimension.png');
        $response2->assertOk();
        $this->assertEquals('image/png', $response2->headers->get('Content-Type'));
    }

    public function test_serves_product_dimension_image_with_dim_suffix(): void
    {
        $cadPng = $this->createPngBinary();
        Http::fake([
            'https://cdn.example.com/cad-dim.png' => Http::response($cadPng, 200, ['Content-Type' => 'image/png']),
        ]);

        Product::factory()->create([
            'slug' => 'strip-fitting',
            'product_code' => 'SF-100',
            'product_dimension' => ['https://cdn.example.com/cad-dim.png'],
            'status' => 'publish',
        ]);

        $response = $this->get('/products/SF-100-dim.webp');
        $response->assertOk();
        $this->assertEquals('image/webp', $response->headers->get('Content-Type'));
    }

    public function test_serves_gallery_image_by_index(): void
    {
        $img1 = $this->createPngBinary(5, 5);
        $img2 = $this->createPngBinary(8, 8);

        Http::fake([
            'https://cdn.example.com/gallery1.png' => Http::response($img1, 200, ['Content-Type' => 'image/png']),
            'https://cdn.example.com/gallery2.png' => Http::response($img2, 200, ['Content-Type' => 'image/png']),
        ]);

        Product::factory()->create([
            'slug' => 'multi-photo-product',
            'product_code' => 'MPP-01',
            'product_images' => [
                'https://cdn.example.com/gallery1.png',
                'https://cdn.example.com/gallery2.png',
            ],
            'status' => 'publish',
        ]);

        $response = $this->get('/products/MPP-01-1.jpg');
        $response->assertOk();
        $this->assertEquals('image/jpeg', $response->headers->get('Content-Type'));
    }

    public function test_download_query_sets_attachment_disposition_with_clean_filename(): void
    {
        $png = $this->createPngBinary();
        Http::fake([
            'https://cdn.example.com/cad.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
            'https://cdn.example.com/cover.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);

        Product::factory()->create([
            'slug' => 'recessed-channel',
            'product_code' => 'RC009',
            'cover' => 'https://cdn.example.com/cover.png',
            'product_dimension' => ['https://cdn.example.com/cad.png'],
            'status' => 'publish',
        ]);

        // Dimension download
        $dimResponse = $this->get('/products/RC009-dimension.png?download');
        $dimResponse->assertOk();
        $this->assertEquals('attachment; filename="rc009-dimension.png"', $dimResponse->headers->get('Content-Disposition'));

        // Cover download with ?dl=1
        $coverResponse = $this->get('/products/RC009.jpg?dl=1');
        $coverResponse->assertOk();
        $this->assertEquals('attachment; filename="rc009.jpg"', $coverResponse->headers->get('Content-Disposition'));
    }

    public function test_returns_304_when_if_none_match_matches_etag(): void
    {
        $png = $this->createPngBinary();
        Http::fake([
            'https://cdn.example.com/cover.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);

        Product::factory()->create([
            'slug' => 'etag-product',
            'product_code' => 'ETAG-01',
            'cover' => 'https://cdn.example.com/cover.png',
            'status' => 'publish',
        ]);

        $firstResponse = $this->get('/products/ETAG-01.jpg');
        $firstResponse->assertOk();
        $etag = $firstResponse->headers->get('ETag');
        $this->assertNotEmpty($etag);

        $secondResponse = $this->withHeaders(['If-None-Match' => $etag])->get('/products/ETAG-01.jpg');
        $secondResponse->assertStatus(304);
    }

    public function test_returns_404_when_dimension_is_missing(): void
    {
        Product::factory()->create([
            'slug' => 'no-dimension-product',
            'product_code' => 'NODIM-01',
            'product_dimension' => null,
            'status' => 'publish',
        ]);

        $this->get('/products/NODIM-01-dimension.jpg')->assertNotFound();
    }

    public function test_returns_404_when_product_does_not_exist(): void
    {
        $this->get('/products/NON-EXISTENT-SKU.jpg')->assertNotFound();
        $this->get('/products/NON-EXISTENT-SKU-dimension.png')->assertNotFound();
    }

    public function test_draft_product_returns_404_in_production(): void
    {
        $png = $this->createPngBinary();
        Http::fake([
            'https://cdn.example.com/draft.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
        ]);

        Product::factory()->create([
            'slug' => 'draft-image-product',
            'product_code' => 'DRAFT-IMG',
            'cover' => 'https://cdn.example.com/draft.png',
            'status' => 'draft',
        ]);

        $this->app->detectEnvironment(fn (): string => 'production');

        $this->get('/products/DRAFT-IMG.jpg')->assertNotFound();
    }
}
