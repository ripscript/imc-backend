<?php

namespace Tests\Feature\ResponseShape;

use App\Models\Content;
use App\Models\ContentAdditionalFile;
use App\Models\ContentAdditionalImage;
use App\Models\ContentAdditionalLink;
use App\Models\ContentAdditionalText;
use App\Models\ContentAdditionalVideo;
use App\Models\ContentDetailPublication;
use App\Models\ContentTag;
use App\Models\MsKategoriContent;
use App\Models\MsTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ResponseShape\Concerns\AssertsResourceShape;
use Tests\TestCase;

class PublicationResponseShapeTest extends TestCase
{
    use AssertsResourceShape, RefreshDatabase;

    /**
     * Sample Detail Response JSON for GET /api/publication/{id}
     * Documentation for Postman and API consumers.
     */
    public const SAMPLE_DETAIL_RESPONSE_JSON = <<<'JSON'
{
    "success": true,
    "message": "Berhasil mengambil data detail publikasi",
    "data": {
        "id": 1,
        "content_id": 1,
        "title": "Publikasi Perkembangan Industri Mesin 2026",
        "slug": "publikasi-perkembangan-industri-mesin-2026",
        "headline": "Ringkasan eksekutif perkembangan sektor permesinan nasional",
        "description": "<p>Detail isi konten publikasi mengenai pencapaian target industri manufaktur...</p>",
        "is_publicated": true,
        "thumbnail_path": "uploads/publication/thumb_2026.jpg",
        "publish_date": "2026-09-15 10:00:00",
        "is_slider": true,
        "layout_id": 1,
        "kategori_content_id": 1,
        "kategori_title": "Berita Utama",
        "created_by": "Super Admin",
        "updated_by": "Super Admin",
        "created_at": "2026-09-15T03:00:00.000000Z",
        "updated_at": "2026-09-15T03:00:00.000000Z",
        "tag_id": [1],
        "content_tag": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailPublication",
                "content_id": 1,
                "tag_id": 1
            }
        ],
        "contentAdditionalImage": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailPublication",
                "content_id": 1,
                "image_path": "uploads/publication/image1.jpg",
                "order": 1
            }
        ],
        "contentAdditionalVideo": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailPublication",
                "content_id": 1,
                "url": "https://www.youtube.com/watch?v=sample",
                "type": "youtube",
                "order": 1
            }
        ],
        "contentAdditionalFile": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailPublication",
                "content_id": 1,
                "file_path": "uploads/publication/doc.pdf",
                "title": "Lampiran Publikasi",
                "order": 1
            }
        ],
        "contentAdditionalLink": [
            {
                "id": 1,
                "content_type": "App\\Models\\ContentDetailPublication",
                "content_id": 1,
                "title": "Portal Resmi",
                "link": "https://kemenperin.go.id",
                "order": 1
            }
        ],
        "contentAdditionalText": {
            "id": 1,
            "content_type": "App\\Models\\ContentDetailPublication",
            "content_id": 1,
            "description": "Teks tambahan publikasi"
        }
    }
}
JSON;

    /**
     * Sample Request Body JSON for POST /api/publication/
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_CREATE_REQUEST_JSON = <<<'JSON'
{
    "content_id": 1,
    "title": "Publikasi Perkembangan Industri Mesin 2026",
    "slug": "publikasi-perkembangan-industri-mesin-2026",
    "headline": "Ringkasan eksekutif perkembangan sektor permesinan nasional",
    "description": "<p>Detail isi konten publikasi mengenai pencapaian target industri manufaktur...</p>",
    "publish_date": "2026-09-15 10:00:00",
    "is_publicated": true,
    "thumbnail_path": "uploads/publication/thumb_2026.jpg",
    "is_slider": true,
    "kategori_content_id": 1,
    "tag_id": [1]
}
JSON;

    /**
     * Sample Request Body JSON for PUT /api/publication/{id}
     * Headers: Content-Type: application/json, Authorization: Bearer <jwt_token>
     */
    public const SAMPLE_UPDATE_REQUEST_JSON = <<<'JSON'
{
    "title": "Publikasi Perkembangan Industri Mesin 2026 (Revisi)",
    "slug": "publikasi-perkembangan-industri-mesin-2026-revisi",
    "headline": "Ringkasan eksekutif revisi perkembangan sektor permesinan",
    "description": "<p>Detail revisi isi konten publikasi...</p>",
    "publish_date": "2026-09-15 12:00:00",
    "is_publicated": false,
    "thumbnail_path": "uploads/publication/thumb_2026_rev.jpg",
    "is_slider": false
}
JSON;

    protected array $baseExpectedKeys = [
        'id',
        'content_id',
        'title',
        'slug',
        'headline',
        'description',
        'is_publicated',
        'thumbnail_path',
        'publish_date',
        'is_slider',
        'layout_id',
        'kategori_content_id',
        'kategori_title',
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

    public function test_detail_response_has_all_base_resource_keys()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create();
        $publication = ContentDetailPublication::factory()->create([
            'content_id' => $content->id,
        ]);

        $response = $this->getJson("/api/publication/{$publication->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'message', 'data']);

        $data = $response->json('data');
        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);
    }

    public function test_detail_whenloaded_relations_are_populated_and_non_empty()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create();
        $kategori = MsKategoriContent::create(['title' => 'Berita Utama', 'is_active' => true]);
        $tag = MsTag::factory()->create(['title' => 'Tag Test']);

        $publication = ContentDetailPublication::factory()->create([
            'content_id' => $content->id,
            'kategori_content_id' => $kategori->id,
            'has_additional_image' => true,
            'has_additional_video' => true,
            'has_additional_file' => true,
            'has_additional_link' => true,
            'has_additional_text' => true,
        ]);

        ContentTag::factory()->create([
            'content_type' => ContentDetailPublication::class,
            'content_id' => $publication->id,
            'tag_id' => $tag->id,
        ]);

        ContentAdditionalImage::factory()->create([
            'content_type' => ContentDetailPublication::class,
            'content_id' => $publication->id,
        ]);

        ContentAdditionalVideo::factory()->create([
            'content_type' => ContentDetailPublication::class,
            'content_id' => $publication->id,
        ]);

        ContentAdditionalFile::factory()->create([
            'content_type' => ContentDetailPublication::class,
            'content_id' => $publication->id,
        ]);

        ContentAdditionalLink::factory()->create([
            'content_type' => ContentDetailPublication::class,
            'content_id' => $publication->id,
        ]);

        ContentAdditionalText::factory()->create([
            'content_type' => ContentDetailPublication::class,
            'content_id' => $publication->id,
        ]);

        $response = $this->getJson("/api/publication/{$publication->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        // Check that response matches the structure of the documented SAMPLE_DETAIL_RESPONSE_JSON
        $this->assertMatchesSampleJsonStructure(self::SAMPLE_DETAIL_RESPONSE_JSON, $data);

        // Check base keys
        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);

        // Check kategori relationship resolution
        $this->assertEquals('Berita Utama', $data['kategori_title']);

        // Check whenLoaded relationships are loaded and non-empty
        $this->assertArrayHasKey('content_tag', $data, 'whenLoaded content_tag missing');
        $this->assertNotEmpty($data['content_tag'], 'content_tag should not be empty');

        $this->assertArrayHasKey('contentAdditionalImage', $data, 'whenLoaded contentAdditionalImage missing');
        $this->assertNotEmpty($data['contentAdditionalImage'], 'contentAdditionalImage should not be empty');

        $this->assertArrayHasKey('contentAdditionalVideo', $data, 'whenLoaded contentAdditionalVideo missing');
        $this->assertNotEmpty($data['contentAdditionalVideo'], 'contentAdditionalVideo should not be empty');

        $this->assertArrayHasKey('contentAdditionalFile', $data, 'whenLoaded contentAdditionalFile missing');
        $this->assertNotEmpty($data['contentAdditionalFile'], 'contentAdditionalFile should not be empty');

        $this->assertArrayHasKey('contentAdditionalLink', $data, 'whenLoaded contentAdditionalLink missing');
        $this->assertNotEmpty($data['contentAdditionalLink'], 'contentAdditionalLink should not be empty');

        $this->assertArrayHasKey('contentAdditionalText', $data, 'whenLoaded contentAdditionalText missing');
        $this->assertNotNull($data['contentAdditionalText'], 'contentAdditionalText should not be null');
    }

    public function test_create_response_matches_resource_shape_and_verifies_fillable()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create();
        $kategori = MsKategoriContent::create(['title' => 'Berita Kategori', 'is_active' => true]);
        $tag = MsTag::factory()->create();

        // Decode from documented SAMPLE_CREATE_REQUEST_JSON and bind dynamic test IDs
        $payload = json_decode(self::SAMPLE_CREATE_REQUEST_JSON, true);
        $payload['content_id'] = $content->id;
        $payload['kategori_content_id'] = $kategori->id;
        $payload['tag_id'] = [$tag->id];

        $response = $this->postJson('/api/publication/', $payload);

        $response->assertStatus(201);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);

        // Verify that all fillable values from the sample request JSON were assigned and not dropped
        $this->assertEquals($payload['title'], $data['title']);
        $this->assertEquals($payload['slug'], $data['slug']);
        $this->assertEquals($payload['headline'], $data['headline']);
        $this->assertEquals($payload['description'], $data['description']);
        $this->assertTrue($data['is_publicated']);
        $this->assertTrue($data['is_slider']);
        $this->assertEquals($payload['thumbnail_path'], $data['thumbnail_path']);
        $this->assertEquals($kategori->id, $data['kategori_content_id']);
        $this->assertNotNull($data['layout_id'], 'layout_id should be auto-assigned');
    }

    public function test_update_response_matches_resource_shape_and_verifies_fillable()
    {
        $this->actingAsSuperAdmin();

        $content = Content::factory()->create();
        $publication = ContentDetailPublication::factory()->create([
            'content_id' => $content->id,
        ]);

        // Decode from documented SAMPLE_UPDATE_REQUEST_JSON
        $updatePayload = json_decode(self::SAMPLE_UPDATE_REQUEST_JSON, true);

        $response = $this->putJson("/api/publication/{$publication->id}", $updatePayload);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertAllResourceKeys($this->baseExpectedKeys, $data);

        // Verify that all fillable values from the sample update request JSON were persisted
        $this->assertEquals($updatePayload['title'], $data['title']);
        $this->assertEquals($updatePayload['slug'], $data['slug']);
        $this->assertEquals($updatePayload['headline'], $data['headline']);
        $this->assertEquals($updatePayload['description'], $data['description']);
        $this->assertFalse($data['is_publicated']);
    }
}
