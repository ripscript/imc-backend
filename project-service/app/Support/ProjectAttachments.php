<?php

namespace App\Support;

/**
 * Validation + normalization rules for S2 "Lampiran Pendukung" attachments.
 *
 * Attachments reference files uploaded through file-service; this API stores
 * the resulting URL plus display metadata. Items may be sent as plain URL
 * strings (legacy) or objects — both are normalized to objects.
 */
class ProjectAttachments
{
    public const MAX_FILES = 5;

    public const MAX_SIZE_KB = 5120;

    public const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    public static function normalize(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(
            fn ($item) => is_string($item) ? ['file_url' => $item] : (array) $item,
            $items
        ));
    }

    /**
     * @return array<string, string> keyed by input path (attachments.{i}.{field})
     */
    public static function validate(array $items): array
    {
        $errors = [];

        foreach ($items as $i => $item) {
            $url = $item['file_url'] ?? null;
            $extension = strtolower(pathinfo(parse_url((string) $url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                $errors["attachments.{$i}.file_url"] = 'Lampiran #'.($i + 1).' harus berekstensi: '.implode(', ', self::ALLOWED_EXTENSIONS).'.';
            }

            if (isset($item['file_size_kb']) && (int) $item['file_size_kb'] > self::MAX_SIZE_KB) {
                $errors["attachments.{$i}.file_size_kb"] = 'Lampiran #'.($i + 1).' maksimal 5 MB.';
            }
        }

        return $errors;
    }
}
