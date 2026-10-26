<?php

namespace App\Services\DTOs;

class ProjectDTO extends BaseDTO
{
    public ?string $name = null;

    public ?string $description = null;

    public ?int $client_id = null;

    public ?int $partner_id = null;

    public ?int $category_id = null;

    public ?int $workflow_id = null;

    public ?int $pic_user_id = null;

    public ?string $start_date = null;

    public ?string $target_end_date = null;

    public ?string $status = null;

    public ?int $progress = null;

    public ?string $created_by = null;

    public ?string $updated_by = null;

    public array $attachments = [];

    public array $deleted_attachment_ids = [];

    public ?int $content_id = null;

    public ?string $title = null;

    public ?string $slug = null;

    public ?string $thumbnail_path = null;

    public ?bool $is_publicated = null;

    public ?string $publish_date = null;

    public ?int $layout_id = null;

    public ?int $kategori_content_id = null;

    public ?string $duration = null;

    public ?string $location = null;

    public $budget = null;

    public ?string $organization_name = null;

    public ?string $organization_address = null;

    public ?array $tag_id = null;

    public ?bool $has_additional_image = null;

    public ?bool $has_additional_video = null;

    public ?bool $has_additional_file = null;

    public ?bool $has_additional_link = null;

    public ?bool $has_additional_text = null;

    public ?array $content_additional_image = null;

    public ?array $content_additional_video = null;

    public ?array $content_additional_file = null;

    public ?array $content_additional_link = null;

    public $content_additional_text = null;

    public ?array $deleted_content_additional_image_id = null;

    public ?array $deleted_content_additional_video_id = null;

    public ?array $deleted_content_additional_file_id = null;

    public ?array $deleted_content_additional_link_id = null;

    public ?array $deleted_content_additional_text_id = null;
}
