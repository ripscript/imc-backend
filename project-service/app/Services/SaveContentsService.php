<?php

namespace App\Services;

use App\Models\ContentAdditionalFile;
use App\Models\ContentAdditionalImage;
use App\Models\ContentAdditionalLink;
use App\Models\ContentAdditionalText;
use App\Models\ContentAdditionalVideo;
use App\Models\ContentTag;
use App\Models\MsTag;
use Illuminate\Validation\ValidationException;

class SaveContentsService
{
    protected bool $uploadVideoEnabled;

    public function __construct()
    {
        $this->uploadVideoEnabled = (bool) config('app.upload_video_enabled', false);
    }

    private function saveRecord($modelClass, $id, $payload, $user)
    {
        if (empty($id) || $id == 0) {
            $payload['created_by'] = $user;

            return $modelClass::create($payload);
        }

        $record = $modelClass::find($id);
        if (! $record) {
            throw ValidationException::withMessages([
                'error' => class_basename($modelClass).' is invalid.',
            ]);
        }

        $record->fill($payload);
        $record->save();

        return $record;
    }

    private function deleteRecordByIds($modelClass, $ids)
    {
        return $modelClass::whereIn('id', $ids)->delete();
    }

    private function buildAdditionalImagePayload($contentType, $contentId, $data, $user, $key = 0)
    {
        return [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'image_path' => $data['image_path'],
            'description' => $data['description'] ?? '',
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    private function buildAdditionalFilePayload($contentType, $contentId, $data, $user, $key = 0)
    {
        return [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'file_path' => $data['file_path'],
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    private function buildAdditionalLinkPayload($contentType, $contentId, $data, $user, $key = 0)
    {
        return [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'link' => $data['link'],
            'title' => $data['title'],
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    private function buildAdditionalTextPayload($contentType, $contentId, $data, $user, $key = 0)
    {
        return [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'title' => $data['title'] ?? null,
            'description' => $data['description'],
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    private function buildAdditionalVideoPayload($contentType, $contentId, $data, $user, $key = 0)
    {
        $type = $this->uploadVideoEnabled ? $data['type'] : 'link';

        return [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'title' => $data['title'],
            'type' => $type,
            'url' => $type == 'link' ? $data['url'] : null,
            'video_path' => $type == 'upload' ? $data['video_path'] : null,
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    public function syncTags($contentType, $contentId, $tagIds, $user)
    {
        $tagIds = $tagIds ?? [];

        if (count($tagIds) > 0) {
            $totalTagValid = MsTag::whereIn('id', $tagIds)->count();

            if ($totalTagValid !== count($tagIds)) {
                throw ValidationException::withMessages([
                    'tag_id' => ['Terdapat tag yang tidak valid'],
                ]);
            }

            foreach ($tagIds as $tag) {
                ContentTag::firstOrCreate(
                    [
                        'content_type' => $contentType,
                        'content_id' => $contentId,
                        'tag_id' => $tag,
                    ],
                    [
                        'created_by' => $user,
                        'updated_by' => $user,
                    ]
                );
            }

            ContentTag::where([
                'content_type' => $contentType,
                'content_id' => $contentId,
            ])->whereNotIn('tag_id', $tagIds)->delete();
        } else {
            ContentTag::where([
                'content_type' => $contentType,
                'content_id' => $contentId,
            ])->delete();
        }
    }

    public function storeAdditionalContent($contentType, $content, array $data, $user)
    {
        $content->fill([
            'has_additional_image' => $data['has_additional_image'] ?? false,
            'has_additional_video' => $data['has_additional_video'] ?? false,
            'has_additional_link' => $data['has_additional_link'] ?? false,
            'has_additional_file' => $data['has_additional_file'] ?? false,
            'has_additional_text' => $data['has_additional_text'] ?? false,
        ])->save();

        $contentId = $content->id;

        if ($content->has_additional_image && empty($data['content_additional_image'])) {
            throw ValidationException::withMessages([
                'content_additional_image' => 'Minimal harus ada 1 gambar pada konten tambahan.',
            ]);
        }

        if ($content->has_additional_video && empty($data['content_additional_video'])) {
            throw ValidationException::withMessages([
                'content_additional_video' => 'Minimal harus ada 1 video pada konten tambahan.',
            ]);
        }

        if ($content->has_additional_file && empty($data['content_additional_file'])) {
            throw ValidationException::withMessages([
                'content_additional_file' => 'Minimal harus ada 1 file pada konten tambahan.',
            ]);
        }

        if ($content->has_additional_link && empty($data['content_additional_link'])) {
            throw ValidationException::withMessages([
                'content_additional_link' => 'Minimal harus ada 1 link pada konten tambahan.',
            ]);
        }

        if ($content->has_additional_text && empty($data['content_additional_text'])) {
            throw ValidationException::withMessages([
                'content_additional_text' => 'Teks tambahan tidak boleh kosong.',
            ]);
        }

        if ($content->has_additional_image == true) {
            foreach ($data['content_additional_image'] as $key => $value) {
                $payload = $this->buildAdditionalImagePayload($contentType, $contentId, $value, $user, $key);
                $this->saveRecord(ContentAdditionalImage::class, $value['id'] ?? null, $payload, $user);
            }
            if (! empty($data['deleted_content_additional_image_id'])) {
                $this->deleteRecordByIds(ContentAdditionalImage::class, $data['deleted_content_additional_image_id']);
            }
        } else {
            ContentAdditionalImage::where([
                'content_type' => $contentType,
                'content_id' => $contentId,
            ])->delete();
        }

        if ($content->has_additional_video == true) {
            foreach ($data['content_additional_video'] as $key => $value) {
                $payload = $this->buildAdditionalVideoPayload($contentType, $contentId, $value, $user, $key);
                $this->saveRecord(ContentAdditionalVideo::class, $value['id'] ?? null, $payload, $user);
            }
            if (! empty($data['deleted_content_additional_video_id'])) {
                $this->deleteRecordByIds(ContentAdditionalVideo::class, $data['deleted_content_additional_video_id']);
            }
        } else {
            ContentAdditionalVideo::where([
                'content_type' => $contentType,
                'content_id' => $contentId,
            ])->delete();
        }

        if ($content->has_additional_file == true) {
            foreach ($data['content_additional_file'] as $key => $value) {
                $payload = $this->buildAdditionalFilePayload($contentType, $contentId, $value, $user, $key);
                $this->saveRecord(ContentAdditionalFile::class, $value['id'] ?? null, $payload, $user);
            }
            if (! empty($data['deleted_content_additional_file_id'])) {
                $this->deleteRecordByIds(ContentAdditionalFile::class, $data['deleted_content_additional_file_id']);
            }
        } else {
            ContentAdditionalFile::where([
                'content_type' => $contentType,
                'content_id' => $contentId,
            ])->delete();
        }

        if ($content->has_additional_link == true) {
            foreach ($data['content_additional_link'] as $key => $value) {
                $payload = $this->buildAdditionalLinkPayload($contentType, $contentId, $value, $user, $key);
                $this->saveRecord(ContentAdditionalLink::class, $value['id'] ?? null, $payload, $user);
            }
            if (! empty($data['deleted_content_additional_link_id'])) {
                $this->deleteRecordByIds(ContentAdditionalLink::class, $data['deleted_content_additional_link_id']);
            }
        } else {
            ContentAdditionalLink::where([
                'content_type' => $contentType,
                'content_id' => $contentId,
            ])->delete();
        }

        if ($content->has_additional_text == true) {
            $texts = $data['content_additional_text'];
            if (isset($texts['description'])) {
                $texts = [$texts];
            }
            $results = [];
            foreach ($texts as $key => $value) {
                $payload = $this->buildAdditionalTextPayload($contentType, $contentId, $value, $user, $key);
                $results[] = $this->saveRecord(ContentAdditionalText::class, $value['id'] ?? null, $payload, $user);
            }
            if (! empty($data['deleted_content_additional_text_id'])) {
                $this->deleteRecordByIds(ContentAdditionalText::class, $data['deleted_content_additional_text_id']);
            }
        } else {
            ContentAdditionalText::where([
                'content_type' => $contentType,
                'content_id' => $contentId,
            ])->delete();
        }
    }
}
