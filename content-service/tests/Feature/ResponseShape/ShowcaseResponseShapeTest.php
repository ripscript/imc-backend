<?php

namespace Tests\Feature\ResponseShape;

use App\Models\Content;
use App\Models\ContentAdditionalFile;
use App\Models\ContentAdditionalImage;
use App\Models\ContentAdditionalLink;
use App\Models\ContentAdditionalText;
use App\Models\ContentAdditionalVideo;
use App\Models\ContentDetailShowcase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ResponseShape\Concerns\AssertsResourceShape;
use Tests\TestCase;

class ShowcaseResponseShapeTest extends TestCase
{
    use AssertsResourceShape, RefreshDatabase;

    /**
     * Sample Detail Response JSON for GET /api/showcase/{id}
     * Includes whenLoaded additional content relations (images, videos, files, links, text).
     */
    public const SAMPLE_DETAIL_RESPONSE_JSON = <<<'JSON'
{
    "success": true,
    "message": "Berhasil mengambil data detail showcase",
    "data": {
        "id": 1,
        "content_id": 1,
        "title": "Showcase Strategis Pengembangan Industri 2026",
        "slug": "showcase-strategis-pengembangan-industri-2026",
        "description": "<p>Detail showcase pengembangan industri nasional...</p>",
        "thumbnail_path": "uploads/showcase/thumb.jpg",
        "is_publicated": true,
        "publish_date": "2026-09-15 10:00:00",
        "duration": "12 Bulan",
        "location": "Jakarta Pusat",
        "start_date": "2026-01-01",
        "budget": 750000000,
        "organization_address": "Jl. Gatot Subroto No. 52-53",
        "organization_name": "Kementerian Perindustrian",
        "has_additional_image": true,
        "has_additional_video": true,
        "has_additional_file": true,
        "has_additional_link": true,
        "has_additional_text": true,
        "created_by": "Super Admin",
        "updated_by": "Super Admin",
        "created_at": "2026-09-15T03:00:00.000000Z",
        "updated_at": "2026-09-15T03:00:00.000000Z",
        "tag_id": [],
        "contentAdditionalImage": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailShowcase",
                "content_id": 1,
                "image_path": "uploads/showcase-img.jpg",
                "order": 1
            }
        ],
        "contentAdditionalVideo": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailShowcase",
                "content_id": 1,
                "url": "https://www.youtube.com/watch?v=sample",
                "type": "youtube",
                "order": 1
            }
        ],
        "contentAdditionalFile": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailShowcase",
                "content_id": 1,
                "file_path": "uploads/showcase/lampiran.pdf",
                "title": "Lampiran Showcase",
                "order": 1
            }
        ],
        "contentAdditionalLink": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailShowcase",
                "content_id": 1,
                "title": "Laman Showcase",
                "link": "https://kemenperin.go.id/showcase",
                "order": 1
            }
        ],
        "contentAdditionalText": {
            "id": 1,
            "content_type": "App\\Models\\ContentDetailShowcase",
            "content_id": 1,
            "description": "Teks tambahan showcase"
        }
    }
}
JSON;

    /**
     * Sample Request Body JSON for POST /api/showcase/
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_CREATE_REQUEST_JSON = <<<'JSON'
{
    "content_id": 1,
    "title": "Showcase Strategis E2E",
    "slug": "showcase-strategis-e2e",
    "description": "Deskripsi showcase strategis nasional",
    "thumbnail_path": "uploads/showcase.jpg",
    "publish_date": "2026-09-15 10:00:00",
    "is_publicated": true,
    "duration": "12 Bulan",
    "location": "Bandung",
    "start_date": "2026-03-01",
    "budget": 750000000,
    "organization_name": "Balai Diklat Industri",
    "organization_address": "Jl. Kliningan No. 5"
}
JSON;

    /**
     * Sample Request Body JSON for PUT /api/showcase/{id}
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_UPDATE_REQUEST_JSON = <<<'JSON'
{
    "title": "Showcase Diperbarui",
    "slug": "showcase-diperbarui",
    "description": "Deskripsi update showcase",
    "is_publicated": false,
    "duration": "24 Bulan",
    "location": "Surabaya"
}
JSON;

    protected array $baseExpectedKeys = [
        'id',
        'content_id',
        'title',
        'slug',
        'description',
        'thumbnail_path',
        'is_publicated',
        'publish_date',
        'duration',
        'location',
        'start_date',
        'budget',
        'organization_address',
        'organization_name',
        'has_additional_image',
        'has_additional_video',
        'has_additional_file',
        'has_additional_link',
        'has_additional_text',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'tag_id',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLayouts();
    }

    public function test_detail_response_has_all_base_keys()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'showcase',
        ]);

        $showcase = ContentDetailShowcase::factory()->create([
            'content_id' => $content->id,
            'duration' => '6 Bulan',
            'location' => 'Jakarta Pusat',
            'start_date' => '2026-01-01',
            'budget' => 500000000,
            'organization_name' => 'Kementerian Perindustrian',
            'organization_address' => 'Jl. Gatot Subroto No. 52-53',
        ]);

        $response = $this->getJson("/api/showcase/{$showcase->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);

        // Verify showcase-specific fields are preserved and not null
        $this->assertEquals('6 Bulan', $data['duration']);
        $this->assertEquals('Jakarta Pusat', $data['location']);
        $this->assertEquals(500000000, $data['budget']);
        $this->assertEquals('Kementerian Perindustrian', $data['organization_name']);
        $this->assertEquals('Jl. Gatot Subroto No. 52-53', $data['organization_address']);
    }

    public function test_detail_whenloaded_additional_content_populated()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'showcase',
        ]);

        $showcase = ContentDetailShowcase::factory()->create([
            'content_id' => $content->id,
            'has_additional_image' => true,
            'has_additional_video' => true,
            'has_additional_file' => true,
            'has_additional_link' => true,
            'has_additional_text' => true,
        ]);

        ContentAdditionalImage::factory()->create([
            'content_type' => ContentDetailShowcase::class,
            'content_id' => $showcase->id,
            'image_path' => 'uploads/showcase-img.jpg',
        ]);

        ContentAdditionalVideo::factory()->create([
            'content_type' => ContentDetailShowcase::class,
            'content_id' => $showcase->id,
        ]);

        ContentAdditionalFile::factory()->create([
            'content_type' => ContentDetailShowcase::class,
            'content_id' => $showcase->id,
        ]);

        ContentAdditionalLink::factory()->create([
            'content_type' => ContentDetailShowcase::class,
            'content_id' => $showcase->id,
        ]);

        ContentAdditionalText::factory()->create([
            'content_type' => ContentDetailShowcase::class,
            'content_id' => $showcase->id,
        ]);

        $response = $this->getJson("/api/showcase/{$showcase->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        // Verify response matches documented SAMPLE_DETAIL_RESPONSE_JSON structure
        $this->assertMatchesSampleJsonStructure(self::SAMPLE_DETAIL_RESPONSE_JSON, $data);

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);

        // Verify whenLoaded relations
        $this->assertArrayHasKey('contentAdditionalImage', $data);
        $this->assertNotEmpty($data['contentAdditionalImage']);

        $this->assertArrayHasKey('contentAdditionalVideo', $data);
        $this->assertNotEmpty($data['contentAdditionalVideo']);

        $this->assertArrayHasKey('contentAdditionalFile', $data);
        $this->assertNotEmpty($data['contentAdditionalFile']);

        $this->assertArrayHasKey('contentAdditionalLink', $data);
        $this->assertNotEmpty($data['contentAdditionalLink']);

        $this->assertArrayHasKey('contentAdditionalText', $data);
        $this->assertNotEmpty($data['contentAdditionalText']);
    }

    public function test_create_showcase_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'showcase',
        ]);

        // Decode from documented SAMPLE_CREATE_REQUEST_JSON and bind dynamic test IDs
        $payload = json_decode(self::SAMPLE_CREATE_REQUEST_JSON, true);
        $payload['content_id'] = $content->id;

        $response = $this->postJson('/api/showcase/', $payload);

        $response->assertStatus(201);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);
        $this->assertEquals($payload['title'], $data['title']);
        $this->assertEquals($payload['slug'], $data['slug']);
        $this->assertEquals($payload['duration'], $data['duration']);
        $this->assertEquals($payload['location'], $data['location']);
        $this->assertEquals($payload['budget'], $data['budget']);
        $this->assertTrue((bool) $data['is_publicated']);
    }

    public function test_update_showcase_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'showcase',
        ]);

        $showcase = ContentDetailShowcase::factory()->create([
            'content_id' => $content->id,
            'title' => 'Showcase Awal',
            'slug' => 'showcase-awal',
        ]);

        // Decode from documented SAMPLE_UPDATE_REQUEST_JSON
        $updatePayload = json_decode(self::SAMPLE_UPDATE_REQUEST_JSON, true);

        $response = $this->putJson("/api/showcase/{$showcase->id}", $updatePayload);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);
        $this->assertEquals($updatePayload['title'], $data['title']);
        $this->assertEquals($updatePayload['slug'], $data['slug']);
        $this->assertEquals($updatePayload['duration'], $data['duration']);
        $this->assertEquals($updatePayload['location'], $data['location']);
        $this->assertFalse((bool) $data['is_publicated']);
    }
}
