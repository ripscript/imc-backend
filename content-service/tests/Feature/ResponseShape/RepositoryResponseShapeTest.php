<?php

namespace Tests\Feature\ResponseShape;

use App\Models\Content;
use App\Models\ContentDetailRepository;
use App\Models\ContentTag;
use App\Models\MsTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ResponseShape\Concerns\AssertsResourceShape;
use Tests\TestCase;

class RepositoryResponseShapeTest extends TestCase
{
    use AssertsResourceShape, RefreshDatabase;

    /**
     * Sample Detail Response JSON for GET /api/repository/{id}
     */
    public const SAMPLE_DETAIL_RESPONSE_JSON = <<<'JSON'
{
    "success": true,
    "message": "Berhasil mengambil data detail repository",
    "data": {
        "id": 1,
        "content_id": 1,
        "title": "Dokumen Kebijakan Industri 2026",
        "slug": "dokumen-kebijakan-industri-2026",
        "description": "Peraturan Menteri Perindustrian tentang kebijakan industri nasional",
        "file_path": "uploads/repository/kebijakan-2026.pdf",
        "thumbnail_path": "uploads/repository/kebijakan-2026-thumb.jpg",
        "is_publicated": true,
        "publish_date": "2026-09-15 10:00:00",
        "layout_id": 1,
        "tag_id": [1],
        "order": 1,
        "created_by": "Super Admin",
        "updated_by": "Super Admin",
        "created_at": "2026-09-15T03:00:00.000000Z",
        "updated_at": "2026-09-15T03:00:00.000000Z"
    }
}
JSON;

    /**
     * Sample Request Body JSON for POST /api/repository/
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_CREATE_REQUEST_JSON = <<<'JSON'
{
    "content_id": 1,
    "title": "Repository Dokumen E2E",
    "slug": "repository-dokumen-e2e",
    "file_path": "uploads/dokumen.pdf",
    "thumbnail_path": "uploads/dokumen-thumb.jpg",
    "description": "Deskripsi dokumen resmi",
    "publish_date": "2026-09-15 10:00:00",
    "is_publicated": true
}
JSON;

    /**
     * Sample Request Body JSON for PUT /api/repository/{id}
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_UPDATE_REQUEST_JSON = <<<'JSON'
{
    "title": "Dokumen Diperbarui",
    "slug": "dokumen-diperbarui",
    "file_path": "uploads/dokumen-v2.pdf",
    "thumbnail_path": "uploads/dokumen-thumb-v2.jpg",
    "description": "Deskripsi dokumen versi 2",
    "publish_date": "2026-09-15 10:00:00",
    "is_publicated": false
}
JSON;

    protected array $expectedResourceKeys = [
        'id',
        'content_id',
        'title',
        'slug',
        'description',
        'file_path',
        'thumbnail_path',
        'is_publicated',
        'publish_date',
        'layout_id',
        'tag_id',
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

    public function test_detail_response_has_all_resource_keys()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'repository',
        ]);

        $repository = ContentDetailRepository::factory()->create([
            'content_id' => $content->id,
        ]);

        $tag = MsTag::factory()->create();
        ContentTag::factory()->create([
            'content_type' => ContentDetailRepository::class,
            'content_id' => $repository->id,
            'tag_id' => $tag->id,
        ]);

        $response = $this->getJson("/api/repository/{$repository->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        // Verify response matches documented SAMPLE_DETAIL_RESPONSE_JSON structure
        $this->assertMatchesSampleJsonStructure(self::SAMPLE_DETAIL_RESPONSE_JSON, $data);

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);
        $this->assertContains($tag->id, $data['tag_id']);
    }

    public function test_create_repository_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'repository',
        ]);

        // Decode from documented SAMPLE_CREATE_REQUEST_JSON and bind dynamic test IDs
        $payload = json_decode(self::SAMPLE_CREATE_REQUEST_JSON, true);
        $payload['content_id'] = $content->id;

        $response = $this->postJson('/api/repository/', $payload);

        $response->assertStatus(201);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);
        $this->assertEquals($payload['title'], $data['title']);
        $this->assertEquals($payload['slug'], $data['slug']);
        $this->assertEquals($payload['description'], $data['description']);
        $this->assertEquals($payload['file_path'], $data['file_path']);
        $this->assertEquals($payload['thumbnail_path'], $data['thumbnail_path']);
        $this->assertTrue((bool) $data['is_publicated']);
        $this->assertDatabaseHas('content_detail_repositories', [
            'id' => $data['id'],
            'description' => $payload['description'],
        ]);
    }

    public function test_update_repository_matches_resource_shape()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create([
            'type' => 'repository',
        ]);

        $repository = ContentDetailRepository::factory()->create([
            'content_id' => $content->id,
            'title' => 'Dokumen Awal',
            'slug' => 'dokumen-awal',
        ]);

        // Decode from documented SAMPLE_UPDATE_REQUEST_JSON
        $updatePayload = json_decode(self::SAMPLE_UPDATE_REQUEST_JSON, true);

        $response = $this->putJson("/api/repository/{$repository->id}", $updatePayload);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->expectedResourceKeys, $data);
        $this->assertEquals($updatePayload['title'], $data['title']);
        $this->assertEquals($updatePayload['slug'], $data['slug']);
        $this->assertEquals($updatePayload['description'], $data['description']);
        $this->assertEquals($updatePayload['file_path'], $data['file_path']);
        $this->assertFalse((bool) $data['is_publicated']);
        $this->assertDatabaseHas('content_detail_repositories', [
            'id' => $repository->id,
            'description' => $updatePayload['description'],
        ]);
    }
}
