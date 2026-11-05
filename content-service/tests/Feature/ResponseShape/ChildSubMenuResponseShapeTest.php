<?php

namespace Tests\Feature\ResponseShape;

use App\Models\ChildSubMenu;
use App\Models\Content;
use App\Models\ContentAdditionalImage;
use App\Models\ContentDetailText;
use App\Models\Menu;
use App\Models\MsLayout;
use App\Models\SubMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ResponseShape\Concerns\AssertsResourceShape;
use Tests\TestCase;

class ChildSubMenuResponseShapeTest extends TestCase
{
    use AssertsResourceShape, RefreshDatabase;

    /**
     * Sample Detail Response JSON for GET /api/child-sub-menu/{id}
     * Demonstrates HasContentMapping and HasAdditionalContentMapping trait resolution.
     */
    public const SAMPLE_DETAIL_RESPONSE_JSON = <<<'JSON'
{
    "success": true,
    "message": "Berhasil mengambil data child sub menu",
    "data": {
        "id": 1,
        "sub_menu_id": 1,
        "title": "Child Sub Menu Profil",
        "description": "Child Sub Menu Profil Deskripsi",
        "slug": "child-sub-menu-profil",
        "is_active": true,
        "order": 1,
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
            "content": "Sample ChildSubMenu Content Text"
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
                "image_path": "uploads/child-submenu-sample.jpg",
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
     * Sample Request Body JSON for POST /api/child-sub-menu/
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_CREATE_REQUEST_JSON = <<<'JSON'
{
    "sub_menu_id": 1,
    "title": "Child Sub Menu Baru E2E",
    "slug": "child-sub-menu-baru-e2e",
    "order": 1,
    "is_active": true,
    "type": "text",
    "layout_id": 1,
    "is_accessible": true,
    "content": {
        "content": "Teks konten child sub menu baru"
    },
    "has_additional_image": false
}
JSON;

    /**
     * Sample Request Body JSON for PUT /api/child-sub-menu/{id}
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_UPDATE_REQUEST_JSON = <<<'JSON'
{
    "sub_menu_id": 1,
    "title": "Child Sub Menu Diperbarui",
    "slug": "child-sub-menu-diperbarui",
    "order": 2,
    "is_active": true,
    "type": "text",
    "layout_id": 1,
    "content": {
        "content": "Teks update child sub menu"
    }
}
JSON;

    protected array $expectedResourceKeys = [
        'id',
        'sub_menu_id',
        'title',
        'description',
        'slug',
        'is_active',
        'order',
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

        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create([
            'menu_id' => $menu->id,
            'has_child' => true,
        ]);
        $childSubMenu = ChildSubMenu::factory()->create([
            'sub_menu_id' => $subMenu->id,
        ]);

        $layout = MsLayout::findByType('text');

        $content = Content::factory()->create([
            'parent_type' => ChildSubMenu::class,
            'parent_id' => $childSubMenu->id,
            'type' => 'text',
            'layout_id' => $layout->id,
            'is_accessible' => true,
            'has_additional_image' => true,
        ]);

        ContentDetailText::create([
            'content_id' => $content->id,
            'content' => 'Sample ChildSubMenu Content Text',
            'created_by' => 'admin',
            'updated_by' => 'admin',
        ]);

        ContentAdditionalImage::factory()->create([
            'content_type' => Content::class,
            'content_id' => $content->id,
            'image_path' => 'uploads/child-submenu-sample.jpg',
            'order' => 1,
        ]);

        $response = $this->getJson("/api/child-sub-menu/{$childSubMenu->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        // Verify response matches documented SAMPLE_DETAIL_RESPONSE_JSON structure
        $this->assertMatchesSampleJsonStructure(self::SAMPLE_DETAIL_RESPONSE_JSON, $data);

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);

        // Verify trait mapping
        $this->assertEquals('text', $data['type']);
        $this->assertEquals($layout->id, $data['layout_id']);
        $this->assertTrue((bool) $data['is_accessible']);
        $this->assertNotNull($data['content']);
        $this->assertEquals('Sample ChildSubMenu Content Text', $data['content']['content']);

        // Verify additional content mapping
        $this->assertTrue((bool) $data['has_additional_image']);
        $this->assertNotEmpty($data['content_additional_image']);
        $this->assertEquals('uploads/child-submenu-sample.jpg', $data['content_additional_image'][0]['image_path']);
    }

    public function test_detail_response_defaults_when_no_content_linked()
    {
        $this->actingAsSuperAdmin();

        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create([
            'menu_id' => $menu->id,
            'has_child' => true,
        ]);
        $childSubMenu = ChildSubMenu::factory()->create([
            'sub_menu_id' => $subMenu->id,
        ]);

        $response = $this->getJson("/api/child-sub-menu/{$childSubMenu->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);

        $this->assertNull($data['type']);
        $this->assertNull($data['content']);
        $this->assertFalse($data['has_additional_image']);
        $this->assertIsArray($data['content_additional_image']);
        $this->assertEmpty($data['content_additional_image']);
    }

    public function test_create_child_sub_menu_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create([
            'menu_id' => $menu->id,
            'has_child' => true,
        ]);
        $layout = MsLayout::findByType('text');

        // Decode from documented SAMPLE_CREATE_REQUEST_JSON and bind dynamic test IDs
        $payload = json_decode(self::SAMPLE_CREATE_REQUEST_JSON, true);
        $payload['sub_menu_id'] = $subMenu->id;
        $payload['layout_id'] = $layout->id;

        $response = $this->postJson('/api/child-sub-menu/', $payload);

        $response->assertStatus(201);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);

        $this->assertEquals($payload['title'], $data['title']);
        $this->assertEquals($payload['slug'], $data['slug']);
        $this->assertEquals($subMenu->id, $data['sub_menu_id']);
    }

    public function test_update_child_sub_menu_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $menu = Menu::factory()->create();
        $subMenu = SubMenu::factory()->create([
            'menu_id' => $menu->id,
            'has_child' => true,
        ]);
        $childSubMenu = ChildSubMenu::factory()->create([
            'sub_menu_id' => $subMenu->id,
            'title' => 'Child Sub Menu Awal',
            'slug' => 'child-sub-menu-awal',
        ]);

        $layout = MsLayout::findByType('text');

        Content::factory()->create([
            'parent_type' => ChildSubMenu::class,
            'parent_id' => $childSubMenu->id,
            'type' => 'text',
            'layout_id' => $layout->id,
        ]);

        // Decode from documented SAMPLE_UPDATE_REQUEST_JSON and bind dynamic test IDs
        $updatePayload = json_decode(self::SAMPLE_UPDATE_REQUEST_JSON, true);
        $updatePayload['sub_menu_id'] = $subMenu->id;
        $updatePayload['layout_id'] = $layout->id;

        $response = $this->putJson("/api/child-sub-menu/{$childSubMenu->id}", $updatePayload);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);
        $this->assertEquals($updatePayload['title'], $data['title']);
        $this->assertEquals($updatePayload['slug'], $data['slug']);
    }
}
