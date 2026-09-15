<?php

namespace Tests\Feature;

use App\Models\Project;
use Database\Seeders\PageSeeder;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RecoveredProjectImagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string, 2: list<string>}>
     */
    public static function recoveredProjects(): array
    {
        return [
            'gables hockey' => [
                'gables-hockey-complex',
                'assets/img/projects/gables-hockey-complex/cover.png',
                ['assets/img/projects/gables-hockey-complex/cover.png'],
            ],
            'made marrickville' => [
                'made-marrickville-warehouse-complex',
                'assets/img/projects/made-marrickville-warehouse-complex/cover.png',
                [
                    'assets/img/projects/made-marrickville-warehouse-complex/gallery-1.png',
                    'assets/img/projects/made-marrickville-warehouse-complex/gallery-2.png',
                    'assets/img/projects/made-marrickville-warehouse-complex/gallery-3.png',
                ],
            ],
            'surry hills' => [
                'surry-hills-village',
                'assets/img/projects/surry-hills-village/cover.jpg',
                [
                    'assets/img/projects/surry-hills-village/gallery-1.jpg',
                    'assets/img/projects/surry-hills-village/gallery-2.jpg',
                    'assets/img/projects/surry-hills-village/gallery-3.jpg',
                ],
            ],
            'westin maldives' => [
                'westin-maldives-miriandhoo-resort',
                'assets/img/projects/westin-maldives-miriandhoo-resort/cover.png',
                [
                    'assets/img/projects/westin-maldives-miriandhoo-resort/gallery-1.png',
                    'assets/img/projects/westin-maldives-miriandhoo-resort/gallery-2.png',
                    'assets/img/projects/westin-maldives-miriandhoo-resort/gallery-3.png',
                ],
            ],
        ];
    }

    /**
     * @param  list<string>  $gallery
     */
    #[DataProvider('recoveredProjects')]
    public function test_seeded_covers_and_galleries_are_local_files(string $slug, string $cover, array $gallery): void
    {
        $this->seed([PageSeeder::class, ProjectSeeder::class]);

        $this->assertFileExists(public_path($cover));
        foreach ($gallery as $image) {
            $this->assertFileExists(public_path($image));
        }

        $project = Project::query()->where('slug', $slug)->firstOrFail();

        $this->assertSame($cover, $project->cover);
        $this->assertSame($gallery, $project->gallery);

        $this->get('/projects')
            ->assertOk()
            ->assertSee('/'.$cover, false);

        $detail = $this->get('/project-detail?slug='.$slug)
            ->assertOk()
            ->assertSee('/'.$cover, false);

        foreach ($gallery as $image) {
            if ($image === $cover) {
                continue;
            }

            $detail->assertSee('/'.$image, false);
        }
    }

    public function test_seeder_restores_remote_covers_to_local_files(): void
    {
        $this->seed(ProjectSeeder::class);

        $project = Project::query()->where('slug', 'gables-hockey-complex')->firstOrFail();
        $project->update([
            'cover' => 'https://i0.wp.com/azoogi.com.au/wp-content/uploads/2025/06/Picture21.png?fit=403%2C253&ssl=1',
            'gallery' => [],
        ]);

        $this->seed(ProjectSeeder::class);

        $project->refresh();

        $this->assertSame('assets/img/projects/gables-hockey-complex/cover.png', $project->cover);
        $this->assertSame(['assets/img/projects/gables-hockey-complex/cover.png'], $project->gallery);
    }

    public function test_seeder_does_not_replace_uploaded_covers(): void
    {
        $this->seed(ProjectSeeder::class);

        $project = Project::query()->where('slug', 'gables-hockey-complex')->firstOrFail();
        $project->update([
            'cover' => '/storage/projects/gables-hockey-complex/cover/abc.png',
        ]);

        $this->seed(ProjectSeeder::class);

        $this->assertSame(
            '/storage/projects/gables-hockey-complex/cover/abc.png',
            $project->fresh()->cover,
        );
    }
}
