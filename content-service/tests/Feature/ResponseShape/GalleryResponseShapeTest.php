<?php

namespace Tests\Feature\ResponseShape;

use App\Models\Content;
use App\Models\ContentDetailGallery;
use App\Models\ContentDetailGalleryList;
use App\Models\MsTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ResponseShape\Concerns\AssertsResourceShape;
use Tests\TestCase;

class GalleryResponseShapeTest extends TestCase
{
    use AssertsResourceShape, RefreshDatabase;

    /**
     * Sample Detail Response JSON for GET /api/gallery/{id}
     * Includes the whenLoaded `list` array of gallery image items.
     */
    public const SAMPLE_DETAIL_RESPONSE_JSON = <<<'JSON'
{
    "success": true,
    "message": "Berhasil mengambil data detail galeri",
    "data": {
        "id": 1,
        "content_id": 1,
        "title": "Galeri Pameran Industri 2026",
        "slug": "galeri-pameran-industri-2026",
        "description": "Koleksi foto kegiatan pameran industri nasional 2026",
        "is_publicated": true,
        "publish_date": "2026-09-15 10:00:00",
        "layout_id": 1,
        "tag_id": [1],
        "date": "2026-09-15",
        "order": 1,
        "created_by": "Super Admin",
        "updated_by": "Super Admin",
        "created_at": "2026-09-15T03:00:00.000000Z",
        "updated_at": "2026-09-15T03:00:00.000000Z",
        "list": [
            {
                "id": 1,
                "content_detail_gallery_id": 1,
                "image_path": "uploads/gallery-1.jpg",
                "description": "Image 1 description",
                "order": 1
            }
        ]
    }
}
JSON;

    /**
     * Sample Request Body JSON for POST /api/gallery/
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_CREATE_REQUEST_JSON = <<<'JSON'
{
    "content_id": 1,
    "title": "Galeri Baru E2E",
    "slug": "galeri-baru-e2e",
    "description": "Deskripsi Galeri Baru",
    "publish_date": "2026-09-15 10:00:00",
    "is_publicated": true,
    "tag_id": [1],
    "list": [
        {
            "image_path": "uploads/gallery-item.jpg",
            "description": "Foto 1"
        }
    ]
}
JSON;

    /**
     * Sample Request Body JSON for PUT /api/gallery/{id}
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_UPDATE_REQUEST_JSON = <<<'JSON'
{
    "title": "Galeri Diperbarui",
    "slug": "galeri-diperbarui",
    "description": "Deskripsi update",
    "publish_date": "2026-09-15 10:00:00",
    "is_publicated": false,
    "list": [
        {
            "id": 1,
            "image_path": "uploads/new-photo.jpg",
            "description": "Foto Baru"
        }
    ]
}
JSON;

    protected array $baseExpectedKeys = [
        'id',
        'content_id',
        'title',
        'slug',
        'description',
        'is_publicated',
        'publish_date',
        'layout_id',
        'tag_id',
        'date',
        'order',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLayouts();
    }

    public function test_detail_response_has_all_base_keys_and_whenloaded_list()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'gallery',
            'sub_type' => 'image',
        ]);

        $gallery = ContentDetailGallery::factory()->create([
            'content_id' => $content->id,
        ]);

        ContentDetailGalleryList::factory()->create([
            'content_detail_gallery_id' => $gallery->id,
            'image_path' => 'uploads/gallery-1.jpg',
            'description' => 'Image 1 description',
            'order' => 1,
        ]);

        $response = $this->getJson("/api/gallery/{$gallery->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        // Verify response matches documented SAMPLE_DETAIL_RESPONSE_JSON structure
        $this->assertMatchesSampleJsonStructure(self::SAMPLE_DETAIL_RESPONSE_JSON, $data);

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);

        // Verify whenLoaded list is present and non-empty
        $this->assertArrayHasKey('list', $data, 'whenLoaded list relation missing');
        $this->assertNotEmpty($data['list'], 'Gallery list items should not be empty');
        $this->assertEquals('uploads/gallery-1.jpg', $data['list'][0]['image_path']);
    }

    public function test_create_gallery_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'gallery',
            'sub_type' => 'image',
        ]);

        $tag = MsTag::factory()->create();

        // Decode from documented SAMPLE_CREATE_REQUEST_JSON and bind dynamic test IDs
        $payload = json_decode(self::SAMPLE_CREATE_REQUEST_JSON, true);
        $payload['content_id'] = $content->id;
        $payload['tag_id'] = [$tag->id];

        $response = $this->postJson('/api/gallery/', $payload);

        $response->assertStatus(201);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);
        $this->assertEquals($payload['title'], $data['title']);
        $this->assertEquals($payload['slug'], $data['slug']);
        $this->assertTrue((bool) $data['is_publicated']);
    }

    public function test_update_gallery_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'gallery',
            'sub_type' => 'image',
        ]);

        $gallery = ContentDetailGallery::factory()->create([
            'content_id' => $content->id,
            'title' => 'Galeri Awal',
            'slug' => 'galeri-awal',
        ]);

        $listItem = ContentDetailGalleryList::factory()->create([
            'content_detail_gallery_id' => $gallery->id,
            'image_path' => 'uploads/initial.jpg',
            'order' => 1,
        ]);

        // Decode from documented SAMPLE_UPDATE_REQUEST_JSON and bind dynamic test IDs
        $updatePayload = json_decode(self::SAMPLE_UPDATE_REQUEST_JSON, true);
        $updatePayload['list'][0]['id'] = $listItem->id;

        $response = $this->putJson("/api/gallery/{$gallery->id}", $updatePayload);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);
        $this->assertEquals($updatePayload['title'], $data['title']);
        $this->assertEquals($updatePayload['slug'], $data['slug']);
        $this->assertFalse((bool) $data['is_publicated']);
    }
}
