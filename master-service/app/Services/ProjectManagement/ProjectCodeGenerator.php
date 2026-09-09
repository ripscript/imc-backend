<?php

namespace App\Services\ProjectManagement;

class ProjectCodeGenerator
{
    public static function client($id): string
    {
        return 'CL-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    public static function partner($id): string
    {
        return 'PR-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    public static function projectCategory($id): string
    {
        return 'PC-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    public static function documentType($id): string
    {
        return 'DT-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    public static function workflow($id): string
    {
        return 'WF-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    public static function workflowStage($workflowId, $sequence): string
    {
        return 'WS-'.$workflowId.'-'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }

    public static function customField($entityType, $id): string
    {
        return 'cf_'.strtolower($entityType).'_'.$id;
    }
}
