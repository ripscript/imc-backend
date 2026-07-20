<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class SafeFileExtension implements ValidationRule
{
    /**
     * Ekstensi yang diperbolehkan (allowlist)
     */
    protected array $allowedExtensions = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'jpg',
        'jpeg',
        'png',
        'webp',
    ];

    /**
     * Ekstensi berbahaya (defense in depth)
     */
    protected array $dangerousExtensions = [
        'sh', 'bat', 'batch', 'py', 'php', 'pl', 'js', 'html', 'css',
        'exe', 'bin', 'cmd', 'com', 'cpl', 'csh', 'hta', 'jar',
        'lnk', 'msi', 'msp', 'mst', 'pif', 'scr', 'sys',
        'vb', 'vbe', 'vbs', 'wsf', 'wsh',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        // Normalisasi ekstensi
        $extension = strtolower($value->getClientOriginalExtension());

        if (! in_array($extension, $this->allowedExtensions, true)) {
            $fail("The :attribute file type is not allowed.");
            return;
        }

        if (in_array($extension, $this->dangerousExtensions, true)) {
            $fail("The :attribute has a dangerous file extension.");
        }
    }
}
