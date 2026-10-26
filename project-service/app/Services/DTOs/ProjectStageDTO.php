<?php

namespace App\Services\DTOs;

class ProjectStageDTO extends BaseDTO
{
    public $workflow_id = null;

    public array $stages = [];

    public ?string $created_by = null;

    public ?string $updated_by = null;

    public bool $workflowIdProvided = false;

    public function __construct(array $data)
    {
        $this->workflowIdProvided = array_key_exists('workflow_id', $data);
        parent::__construct($data);
    }
}
