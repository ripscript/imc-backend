<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentDetailCardLink;
use App\Models\Menu;
use App\Models\MsLayout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLayouts();
    }

    public function test_can_get_index()
    {
        $beranda = Menu::factory()->create(['slug' => 'beranda']);
        Content::factory()->count(5)->create([
            'parent_type' => Menu::class,
            'parent_id' => $beranda->id,
        ]);
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/section');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_create_section()
    {
        Menu::factory()->create(['slug' => 'beranda']);
        $this->actingAsSuperAdmin();

        $data = [
            'section_title' => 'Test Section',
            'section_description' => 'Test Section Description',
            'is_section' => true,
            'order' => 1,
            'type' => 'card_link',
            'content' => [
                [
                    'title' => 'Test Card',
                    'link' => 'https://example.com',
                ],
            ],
        ];

        $response = $this->postJson('/api/section', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('contents', [
            'section_title' => 'Test Section',
            'section_description' => 'Test Section Description',
        ]);
    }

    public function test_can_create_section_with_card_link()
    {
        Menu::factory()->create(['slug' => 'beranda']);
        $this->actingAsSuperAdmin();

        $data = [
            'section_title' => 'Layanan Unggulan',
            'is_section' => true,
            'order' => 1,
            'type' => 'card_link',
            'content' => [
                [
                    'title' => 'Konsultasi Industri',
                    'color_code' => '#F59E0B',
                    'link' => 'https://layanan/konsultasi-industri',
                    'order' => 1,
                ],
                [
                    'title' => 'Sertifikasi Produk',
                    'color_code' => '#10B981',
                    'link' => 'https://layanan/sertifikasi-produk',
                    'order' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/section', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('content_detail_card_links', [
            'title' => 'Konsultasi Industri',
            'color_code' => '#F59E0B',
            'link' => 'https://layanan/konsultasi-industri',
        ]);
    }

    public function test_can_get_section_detail()
    {
        $content = Content::factory()->create();
        $this->actingAsSuperAdmin();

        $response = $this->getJson("/api/section/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_for_nonexistent_section()
    {
        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/section/99999');

        $response->assertStatus(404);
    }

    public function test_can_update_section()
    {
        $content = Content::factory()->create([
            'type' => 'card_link',
        ]);
        $this->actingAsSuperAdmin();
        $data = [
            'section_title' => 'Updated Section Title',
            'section_description' => 'Updated Section Description',
            'is_section' => true,
            'order' => 1,
            'content' => [
                [
                    'title' => 'Updated Card',
                    'link' => '/updated',
                ],
            ],
        ];

        $response = $this->putJson("/api/section/{$content->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'section_title' => 'Updated Section Title',
            'section_description' => 'Updated Section Description',
        ]);
    }

    public function test_section_description_exceeding_max_fails_validation()
    {
        Menu::factory()->create(['slug' => 'beranda']);
        $this->actingAsSuperAdmin();

        $data = [
            'section_title' => 'Test Section',
            'section_description' => str_repeat('a', 256),
            'is_section' => true,
            'order' => 1,
            'type' => 'card_link',
            'content' => [
                [
                    'title' => 'Test Card',
                    'link' => 'https://example.com',
                ],
            ],
        ];

        $response = $this->postJson('/api/section', $data);

        $response->assertStatus(422);
        $this->assertNotEmpty($response->json());
    }

    public function test_can_update_section_with_card_link()
    {
        $content = Content::factory()->create([
            'type' => 'card_link',
            'section_title' => 'Layanan Unggulan',
        ]);
        $existingDetail = ContentDetailCardLink::create([
            'content_id' => $content->id,
            'title' => 'Old Title',
            'color_code' => '#000000',
            'link' => '/old-link',
            'order' => 1,
        ]);

        $this->actingAsSuperAdmin();

        $data = [
            'id' => $content->id,
            'section_title' => 'Layanan Unggulan Updated',
            'is_section' => true,
            'order' => 6,
            'type' => 'card_link',
            'content' => [
                [
                    'id' => $existingDetail->id,
                    'content_id' => $content->id,
                    'title' => 'Konsultasi Industri',
                    'color_code' => '#F59E0B',
                    'link' => 'https://layanan/konsultasi-industri',
                    'order' => 1,
                ],
                [
                    'title' => 'Sertifikasi Produk',
                    'color_code' => '#10B981',
                    'link' => 'https://layanan/sertifikasi-produk',
                    'order' => 2,
                ],
            ],
        ];

        $response = $this->putJson("/api/section/{$content->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('content_detail_card_links', [
            'id' => $existingDetail->id,
            'title' => 'Konsultasi Industri',
            'color_code' => '#F59E0B',
            'link' => 'https://layanan/konsultasi-industri',
        ]);

        $this->assertDatabaseHas('content_detail_card_links', [
            'content_id' => $content->id,
            'title' => 'Sertifikasi Produk',
            'color_code' => '#10B981',
            'link' => 'https://layanan/sertifikasi-produk',
        ]);
    }

    public function test_can_update_section_with_consume()
    {
        $content = Content::factory()->create([
            'type' => 'consume',
            'section_title' => 'Showcase',
        ]);
        $existing = \App\Models\ContentDetailConsume::create([
            'content_id' => $content->id,
            'consume_id' => 3,
            'consume_type' => 'showcase',
            'order' => 1,
        ]);

        $this->actingAsSuperAdmin();

        $data = [
            'id' => $content->id,
            'section_title' => 'Showcase Updated',
            'is_section' => true,
            'order' => 3,
            'type' => 'consume',
            'content' => [
                [
                    'id' => $existing->id,
                    'content_id' => $content->id,
                    'consume_type' => 'showcase',
                    'consume_id' => 3,
                    'order' => 1,
                ],
                [
                    'content_id' => $content->id,
                    'consume_type' => 'showcase',
                    'consume_id' => 4,
                    'order' => 2,
                ],
            ],
        ];

        $response = $this->putJson("/api/section/{$content->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $this->assertDatabaseHas('content_detail_consumes', [
            'content_id' => $content->id,
            'consume_id' => 3,
            'consume_type' => 'showcase',
        ]);
        $this->assertDatabaseHas('content_detail_consumes', [
            'content_id' => $content->id,
            'consume_id' => 4,
            'consume_type' => 'showcase',
        ]);
    }

    public function test_can_delete_section()
    {
        $content = Content::factory()->create();
        $this->actingAsSuperAdmin();

        $response = $this->deleteJson("/api/section/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_returns_404_when_deleting_nonexistent_section()
    {
        $this->actingAsSuperAdmin();

        $response = $this->deleteJson('/api/section/99999');

        $response->assertStatus(404);
    }
}
