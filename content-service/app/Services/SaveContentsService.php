<?php

namespace App\Services;

use App\Models\ContentAdditionalFile;
use App\Models\ContentAdditionalImage;
use App\Models\ContentAdditionalLink;
use App\Models\ContentAdditionalText;
use App\Models\ContentAdditionalVideo;
use App\Models\ContentDetailAccordionGroup;
use App\Models\ContentDetailAccordionList;
use App\Models\ContentDetailCardLink;
use App\Models\ContentDetailConsume;
use App\Models\ContentDetailHeroCarousel;
use App\Models\ContentDetailImageCarousel;
use App\Models\ContentDetailImageLink;
use App\Models\ContentDetailOrganizationalStructureGroup;
use App\Models\ContentDetailOrganizationalStructureMember;
use App\Models\ContentDetailText;
use App\Models\ContentDetailVideoCarousel;
use App\Models\ContentTag;
use App\Models\MsTag;
use Illuminate\Validation\ValidationException;

class SaveContentsService
{
    protected bool $uploadVideoEnabled;

    public function __construct()
    {
        $this->uploadVideoEnabled = config('app.upload_video_enabled');
    }

    public function store($content, array $data, $user)
    {
        if (! isset($data['content']) && ! in_array($content->type, ['publication', 'repository', 'gallery', 'agenda', 'organizational_structure', 'showcase'])) {
            throw ValidationException::withMessages([
                'error' => 'Content is missing.',
            ]);
        }

        $dataContent = $data['content'];

        switch ($content->type) {
            case 'text':
                $payload = $this->buildTextPayload($content, $dataContent, $user);

                return $this->saveRecord(ContentDetailText::class, $dataContent['id'] ?? null, $payload, $user);
                break;

            case 'card_link':
                $results = [];
                foreach ($dataContent as $key => $value) {
                    $payload = $this->buildCardLinkPayload($content, $value, $user, $key);
                    $results[] = $this->saveRecord(ContentDetailCardLink::class, $value['id'] ?? null, $payload, $user);
                }
                if (! empty($data['deleted_content_detail_id'])) {
                    $this->deleteRecordByIds(ContentDetailCardLink::class, $data['deleted_content_detail_id']);
                }

                return $results;
                break;

            case 'image_link':
                $results = [];
                foreach ($dataContent as $key => $value) {
                    $payload = $this->buildImageLinkPayload($content, $value, $user, $key);
                    $results[] = $this->saveRecord(ContentDetailImageLink::class, $value['id'] ?? null, $payload, $user);
                }
                if (! empty($data['deleted_content_detail_id'])) {
                    $this->deleteRecordByIds(ContentDetailImageLink::class, $data['deleted_content_detail_id']);
                }

                return $results;
                break;

            case 'image_carousel':
                $results = [];
                foreach ($dataContent as $key => $value) {
                    $payload = $this->buildImageCarouselPayload($content, $value, $user, $key);
                    $results[] = $this->saveRecord(ContentDetailImageCarousel::class, $value['id'] ?? null, $payload, $user);
                }
                if (! empty($data['deleted_content_detail_id'])) {
                    $this->deleteRecordByIds(ContentDetailImageCarousel::class, $data['deleted_content_detail_id']);
                }

                return $results;
                break;

            case 'hero_carousel':
                $results = [];
                foreach ($dataContent as $key => $value) {
                    $payload = $this->buildHeroCarouselPayload($content, $value, $user, $key);
                    $results[] = $this->saveRecord(ContentDetailHeroCarousel::class, $value['id'] ?? null, $payload, $user);
                }
                if (! empty($data['deleted_content_detail_id'])) {
                    $this->deleteRecordByIds(ContentDetailHeroCarousel::class, $data['deleted_content_detail_id']);
                }

                return $results;
                break;

            case 'video_carousel':
                $results = [];
                foreach ($dataContent as $key => $value) {
                    $payload = $this->buildVideoCarouselPayload($content, $value, $user, $key);
                    $results[] = $this->saveRecord(ContentDetailVideoCarousel::class, $value['id'] ?? null, $payload, $user);
                }
                if (! empty($data['deleted_content_detail_id'])) {
                    $this->deleteRecordByIds(ContentDetailVideoCarousel::class, $data['deleted_content_detail_id']);
                }

                return $results;
                break;

            case 'accordion':
                $results = [];
                foreach ($dataContent as $key => $value) {
                    $payload = $this->buildAccordionGroupPayload($content, $value, $user, $key);
                    $resultGroup = $this->saveRecord(ContentDetailAccordionGroup::class, $value['id'] ?? null, $payload, $user);
                    $resultLists = [];

                    foreach ($value['list'] as $keyList => $valueList) {
                        $payloadList = $this->buildAccordionListPayload($resultGroup, $valueList, $user, $keyList);
                        $resultList = $this->saveRecord(ContentDetailAccordionList::class, $valueList['id'] ?? null, $payloadList, $user);
                        $resultLists[] = $resultList;
                    }

                    $resultGroup->list = $resultLists;
                    $results[] = $resultGroup;
                }
                if (! empty($data['deleted_content_detail_id'])) {
                    $this->deleteRecordByIds(ContentDetailAccordionGroup::class, $data['deleted_content_detail_id']);
                }
                if (! empty($data['deleted_content_detail_list_id'])) {
                    $this->deleteRecordByIds(ContentDetailAccordionList::class, $data['deleted_content_detail_list_id']);
                }

                return $results;
                break;

            case 'consume':
                $results = [];
                $ids = [];
                foreach ($dataContent as $key => $value) {
                    $consumeId = $value['consume_id'];
                    $consumeType = $value['consume_type'];
                    $result = ContentDetailConsume::updateOrCreate(
                        [
                            'content_id' => $content->id,
                            'consume_id' => $consumeId,
                            'consume_type' => $consumeType,
                        ],
                        [
                            'order' => ($key + 1),
                            'created_by' => $user,
                            'updated_by' => $user,
                        ]
                    );
                    $results[] = $result;
                    $ids[] = $result->id;
                }
                ContentDetailConsume::where('content_id', $content->id)->whereNotIn('id', $ids)->delete();

                return $results;
                break;

            case 'organizational_structure':
                $results = [];
                foreach ($dataContent['groups'] as $key => $group) {
                    $payload = [
                        'content_id' => $content->id,
                        'name' => $group['name'],
                        'order' => $group['order'] ?? ($key + 1),
                        'updated_by' => $user,
                    ];
                    $resultGroup = $this->saveRecord(ContentDetailOrganizationalStructureGroup::class, $group['id'] ?? null, $payload, $user);

                    $resultMembers = [];
                    foreach ($group['members'] as $keyMember => $member) {
                        $memberPayload = [
                            'content_detail_organizational_structure_group_id' => $resultGroup->id,
                            'name' => $member['name'],
                            'level' => $member['level'] ?? 1,
                            'position' => $member['position'] ?? '',
                            'description' => $member['description'] ?? '',
                            'image_path' => $member['image_path'] ?? '',
                            'is_child' => $member['is_child'] ?? ($keyMember > 0),
                            'have_child' => $member['have_child'] ?? ($keyMember === 0),
                            'parent_id' => $member['parent_id'] ?? null,
                            'order' => $member['order'] ?? ($keyMember + 1),
                            'updated_by' => $user,
                        ];
                        $resultMember = $this->saveRecord(ContentDetailOrganizationalStructureMember::class, $member['id'] ?? null, $memberPayload, $user);
                        $resultMembers[] = $resultMember;
                    }

                    if (count($resultMembers) > 1) {
                        $parentId = $resultMembers[0]->id;
                        for ($i = 1; $i < count($resultMembers); $i++) {
                            if (! ($group['members'][$i]['is_child'] ?? true)) {
                                continue;
                            }
                            $resultMembers[$i]->parent_id = $parentId;
                            $resultMembers[$i]->save();
                        }
                    }

                    $resultGroup->members = $resultMembers;
                    $results[] = $resultGroup;
                }
                if (! empty($data['deleted_content_detail_id'])) {
                    $this->deleteRecordByIds(ContentDetailOrganizationalStructureGroup::class, $data['deleted_content_detail_id']);
                }
                if (! empty($data['deleted_content_detail_list_id'])) {
                    $this->deleteRecordByIds(ContentDetailOrganizationalStructureMember::class, $data['deleted_content_detail_list_id']);
                }

                return $results;
                break;

            default:
                return [];
                break;
        }
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

    private function buildTextPayload($content, $data, $user)
    {
        return [
            'content_id' => $content->id,
            'content' => $data['content'],
            'updated_by' => $user,
        ];
    }

    private function buildCardLinkPayload($content, $data, $user, $key = 0)
    {
        return [
            'content_id' => $content->id,
            'title' => $data['title'],
            'color_code' => $data['color_code'],
            'link' => $data['link'],
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    private function buildImageLinkPayload($content, $data, $user, $key = 0)
    {
        return [
            'content_id' => $content->id,
            'title' => $data['title'],
            'image_path' => $data['image_path'],
            'link' => $data['link'],
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    private function buildImageCarouselPayload($content, $data, $user, $key = 0)
    {
        return [
            'content_id' => $content->id,
            'title' => $data['title'],
            'image_path' => $data['image_path'],
            'description' => $data['description'] ?? '',
            'order' => ($key + 1),
            'updated_by' => $user,
            'link' => $data['link'] ?? '',
        ];
    }

    private function buildHeroCarouselPayload($content, $data, $user, $key = 0)
    {
        return [
            'content_id' => $content->id,
            'source_type' => $data['source_type'] ?? null,
            'source_id' => $data['source_id'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    private function buildVideoCarouselPayload($content, $data, $user, $key = 0)
    {
        $type = $this->uploadVideoEnabled ? $data['type'] : 'link';

        return [
            'content_id' => $content->id,
            'title' => $data['title'],
            'order' => ($key + 1),
            'updated_by' => $user,
            'type' => $type,
            'url' => $type == 'link' ? $data['url'] : null,
            'video_path' => $type == 'upload' ? $data['video_path'] : null,
        ];
    }

    private function buildAccordionGroupPayload($content, $data, $user, $key = 0)
    {
        return [
            'content_id' => $content->id,
            'title' => $data['title'],
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
    }

    private function buildAccordionListPayload($accordionGroup, $data, $user, $key = 0)
    {
        return [
            'content_detail_accordion_group_id' => $accordionGroup->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'file_path' => $data['file_path'] ?? '',
            'link' => $data['link'] ?? '',
            'order' => ($key + 1),
            'updated_by' => $user,
        ];
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

        // validasi
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
