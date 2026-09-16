<?php

namespace Tests\Feature\ResponseShape;

use App\Models\Content;
use App\Models\ContentAdditionalImage;
use App\Models\ContentDetailText;
use App\Models\Menu;
use App\Models\MsLayout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ResponseShape\Concerns\AssertsResourceShape;
use Tests\TestCase;

class MenuResponseShapeTest extends TestCase
{
    use AssertsResourceShape, RefreshDatabase;

    /**
     * Sample Detail Response JSON for GET /api/menu/{id}
     * Demonstrates HasContentMapping and HasAdditionalContentMapping trait resolution.
     */
    public const SAMPLE_DETAIL_RESPONSE_JSON = <<<'JSON'
{
    "success": true,
    "message": "Berhasil mengambil data menu",
    "data": {
        "id": 1,
        "title": "Profil Industri",
        "slug": "profil-industri",
        "is_active": true,
        "order": 1,
        "has_sub": false,
        "is_show_front": true,
        "is_show_side_menu": true,
        "is_dedicated": false,
        "created_by": "Super Admin",
        "updated_by": "Super Admin",
        "created_at": "2026-09-15T03:00:00.000000Z",
        "updated_at": "2026-09-15T03:00:00.000000Z",
        "type": "text",
        "layout_id": 1,
        "is_accessible": true,
        "is_show_home": false,
        "is_show_tag": false,
        "sub_type": null,
        "content": {
            "id": 1,
            "content_id": 1,
            "content": "Sample Text Content for Menu"
        },
        "has_additional_image": true,
        "has_additional_video": false,
        "has_additional_link": false,
        "has_additional_file": false,
        "has_additional_text": false,
        "content_additional_image": [
            {
                "id": 1,
                "content_type": "App\\Models\\Content",
                "content_id": 1,
                "image_path": "uploads/sample.jpg",
                "order": 1
            }
        ],
        "content_additional_video": [],
        "content_additional_link": [],
        "content_additional_file": [],
        "content_additional_text": null
    }
}
JSON;

    /**
     * Sample Request Body JSON for POST /api/menu/
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_CREATE_REQUEST_JSON = <<<'JSON'
{
    "title": "Menu Baru E2E",
    "slug": "menu-baru-e2e",
    "order": 1,
    "is_active": true,
    "has_sub": false,
    "is_show_front": false,
    "type": "text",
    "layout_id": 1,
    "is_accessible": true,
    "content": {
        "content": "Teks konten menu baru"
    },
    "has_additional_image": false
}
JSON;

    /**
     * Sample Request Body JSON for PUT /api/menu/{id}
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_UPDATE_REQUEST_JSON = <<<'JSON'
{
    "title": "Menu Diperbarui",
    "slug": "menu-diperbarui",
    "order": 2,
    "is_active": true,
    "has_sub": false,
    "is_show_front": false,
    "type": "text",
    "layout_id": 1,
    "content": {
        "content": "Teks update"
    }
}
JSON;

    protected array $expectedResourceKeys = [
        'id',
        'title',
        'slug',
        'is_active',
        'order',
        'has_sub',
        'is_show_front',
        'is_show_side_menu',
        'is_dedicated',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'type',
        'layout_id',
        'is_accessible',
        'is_show_home',
        'is_show_tag',
        'sub_type',
        'content',
        'has_additional_image',
        'has_additional_video',
        'has_additional_link',
        'has_additional_file',
        'has_additional_text',
        'content_additional_image',
        'content_additional_video',
        'content_additional_link',
        'content_additional_file',
        'content_additional_text',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLayouts();
    }

    public function test_detail_response_has_all_keys_and_trait_mapping_when_content_linked()
    {
        $this->actingAsSuperAdmin();

        $menu = Menu::factory()->create([
            'has_sub' => false,
        ]);

        $layout = MsLayout::findByType('text');

        $content = Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'type' => 'text',
            'layout_id' => $layout->id,
            'is_accessible' => true,
            'is_show_home' => false,
            'is_show_tag' => false,
            'has_additional_image' => true,
            'has_additional_video' => false,
            'has_additional_file' => false,
            'has_additional_link' => false,
            'has_additional_text' => false,
        ]);

        ContentDetailText::create([
            'content_id' => $content->id,
            'content' => 'Sample Text Content for Menu',
            'created_by' => 'admin',
            'updated_by' => 'admin',
        ]);

        ContentAdditionalImage::factory()->create([
            'content_type' => Content::class,
            'content_id' => $content->id,
            'image_path' => 'uploads/sample.jpg',
            'order' => 1,
        ]);

        $response = $this->getJson("/api/menu/{$menu->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        // Verify response matches documented SAMPLE_DETAIL_RESPONSE_JSON structure
        $this->assertMatchesSampleJsonStructure(self::SAMPLE_DETAIL_RESPONSE_JSON, $data);

        // Assert all resource keys exist
        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);

        // Verify trait mapping from HasContentMapping
        $this->assertEquals('text', $data['type'], 'Trait HasContentMapping failed to map type');
        $this->assertEquals($layout->id, $data['layout_id'], 'Trait HasContentMapping failed to map layout_id');
        $this->assertTrue((bool) $data['is_accessible']);
        $this->assertNotNull($data['content'], 'Trait HasContentMapping failed to populate content_detail');
        $this->assertEquals('Sample Text Content for Menu', $data['content']['content']);

        // Verify trait mapping from HasAdditionalContentMapping
        $this->assertTrue((bool) $data['has_additional_image'], 'Trait HasAdditionalContentMapping failed to map flag');
        $this->assertNotEmpty($data['content_additional_image'], 'content_additional_image should not be empty');
        $this->assertEquals('uploads/sample.jpg', $data['content_additional_image'][0]['image_path']);
    }

    public function test_detail_response_defaults_when_no_content_linked()
    {
        $this->actingAsSuperAdmin();

        $menu = Menu::factory()->create([
            'has_sub' => false,
        ]);

        $response = $this->getJson("/api/menu/{$menu->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);

        // Verify graceful defaults when no content exists
        $this->assertNull($data['type']);
        $this->assertNull($data['content']);
        $this->assertFalse($data['has_additional_image']);
        $this->assertIsArray($data['content_additional_image']);
        $this->assertEmpty($data['content_additional_image']);
    }

    public function test_create_menu_with_content_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $layout = MsLayout::findByType('text');

        // Decode from documented SAMPLE_CREATE_REQUEST_JSON and bind dynamic test IDs
        $payload = json_decode(self::SAMPLE_CREATE_REQUEST_JSON, true);
        $payload['layout_id'] = $layout->id;

        $response = $this->postJson('/api/menu/', $payload);

        $response->assertStatus(201);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);

        $this->assertEquals($payload['title'], $data['title']);
        $this->assertEquals($payload['slug'], $data['slug']);
        $this->assertFalse($data['has_sub']);
    }

    public function test_update_menu_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $menu = Menu::factory()->create([
            'title' => 'Menu Awal',
            'slug' => 'menu-awal',
            'has_sub' => false,
        ]);

        $layout = MsLayout::findByType('text');

        Content::factory()->create([
            'parent_type' => Menu::class,
            'parent_id' => $menu->id,
            'type' => 'text',
            'layout_id' => $layout->id,
        ]);

        // Decode from documented SAMPLE_UPDATE_REQUEST_JSON and bind dynamic test IDs
        $updatePayload = json_decode(self::SAMPLE_UPDATE_REQUEST_JSON, true);
        $updatePayload['layout_id'] = $layout->id;

        $response = $this->putJson("/api/menu/{$menu->id}", $updatePayload);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);
        $this->assertEquals($updatePayload['title'], $data['title']);
        $this->assertEquals($updatePayload['slug'], $data['slug']);
    }
}
