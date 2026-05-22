<?php

namespace Tests\Feature;

use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicGuideApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_list_only_returns_published_guides(): void
    {
        Guide::create([
            'title' => 'Panduan Lama',
            'slug' => 'panduan-lama',
            'excerpt' => 'Ringkasan panduan lama.',
            'body' => 'Isi panduan lama.',
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);

        Guide::create([
            'title' => 'Panduan Baru',
            'slug' => 'panduan-baru',
            'excerpt' => 'Ringkasan panduan baru.',
            'body' => 'Isi panduan baru.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        Guide::create([
            'title' => 'Draft Tersembunyi',
            'slug' => 'draft-tersembunyi',
            'excerpt' => 'Ringkasan draft.',
            'body' => 'Isi draft.',
            'status' => 'draft',
        ]);

        $this->getJson('/api/guides')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'panduan-baru')
            ->assertJsonPath('data.0.summary', 'Ringkasan panduan baru.')
            ->assertJsonPath('data.0.status', 'published')
            ->assertJsonMissing(['slug' => 'draft-tersembunyi'])
            ->assertJsonMissing(['body' => 'Isi panduan baru.']);
    }

    public function test_guide_detail_shows_published_guide(): void
    {
        $guide = Guide::create([
            'title' => 'Belajar Notes',
            'slug' => 'belajar-notes',
            'excerpt' => 'Ringkasan belajar notes.',
            'body' => 'Isi panduan notes.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->getJson('/api/guides/'.$guide->slug)
            ->assertOk()
            ->assertJsonPath('data.title', 'Belajar Notes')
            ->assertJsonPath('data.slug', 'belajar-notes')
            ->assertJsonPath('data.summary', 'Ringkasan belajar notes.')
            ->assertJsonPath('data.body', 'Isi panduan notes.');
    }

    public function test_unpublished_guide_is_not_publicly_accessible(): void
    {
        $guide = Guide::create([
            'title' => 'Draft Panduan',
            'slug' => 'draft-panduan',
            'excerpt' => 'Ringkasan draft.',
            'body' => 'Isi draft.',
            'status' => 'draft',
        ]);

        $this->getJson('/api/guides/'.$guide->slug)->assertNotFound();
    }
}
