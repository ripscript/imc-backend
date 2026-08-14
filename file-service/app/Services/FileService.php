<?php

namespace App\Services;

use App\Exceptions\Domain\EntityNotFoundException;
use App\Exceptions\Domain\ValidationException;
use App\Helpers\File;
use App\Services\DTOs\CompressImageDTO;
use App\Services\DTOs\FileAccessDTO;
use App\Services\DTOs\LockFileDTO;
use App\Services\DTOs\UploadFileDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use TCPDF;

class FileService
{
    const DEFAULT_MAX_FILE_SIZE = 10; // in MB

    public function getMaxFileSize(): int
    {
        return (int) DB::table('site_settings')
            ->value('max_file_size')
            ?? self::DEFAULT_MAX_FILE_SIZE;
    }

    public function upload(UploadFileDTO $dto): array
    {
        $uploadedFile = $dto->file;

        if (! $uploadedFile || ! $uploadedFile->isValid()) {
            throw new ValidationException('File tidak valid atau tidak ditemukan');
        }

        $maxFileSize = $this->getMaxFileSize();
        $maxSizeInBytes = $maxFileSize * 1024 * 1024;
        if ($uploadedFile->getSize() > $maxSizeInBytes) {
            throw new ValidationException('Ukuran file tidak boleh lebih dari '.$maxFileSize.' MB');
        }

        $path = $dto->path ?? 'uploads';

        $filename = File::generateFilename($uploadedFile->getClientOriginalName());
        $relativePath = $uploadedFile->storeAs($path, $filename, 'public');
        $publicPath = Storage::url($relativePath);

        $url = url($publicPath);
        $baseUrlBefore = config('app.base_url_before');
        $baseUrlAfter = config('app.base_url_after');
        $newUrl = str_replace($baseUrlBefore, $baseUrlAfter, $url);

        return [
            'url' => $newUrl,
            'path' => $relativePath,
        ];
    }

    public function compressImage(CompressImageDTO $dto): array
    {
        $image = $dto->file;

        if (! $image || strpos($image->getMimeType(), 'image') === false) {
            throw new ValidationException('Tidak ada gambar atau file tidak valid');
        }

        $imageName = File::generateFilename($image->getClientOriginalName());
        $imageExtension = $image->getClientOriginalExtension();

        $pathFolder = $dto->path ?? 'compressed_images';
        $relativePath = $image->storeAs($pathFolder, $imageName, 'public');

        $widthLimit = 1920;
        $heightLimit = 1080;
        $maxFileSize = 2 * 1024 * 1024;

        $compressedImage = Image::make($image);

        if ($compressedImage->width() > $widthLimit || $compressedImage->height() > $heightLimit) {
            $compressedImage->resize(1920, 1080, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        if ($compressedImage->filesize() > $maxFileSize) {
            $compressedImage->encode($imageExtension, 50);
        }
        Storage::disk('public')->put($relativePath, $compressedImage->stream());
        $url = url(Storage::url($relativePath));
        $baseUrlBefore = config('app.base_url_before');
        $baseUrlAfter = config('app.base_url_after');
        $newUrl = str_replace($baseUrlBefore, $baseUrlAfter, $url);

        return [
            'url' => $newUrl,
            'path' => $relativePath,
        ];
    }

    public function download(FileAccessDTO $dto): array
    {
        $path = 'public/'.$dto->file;

        if (! Storage::exists($path)) {
            throw new EntityNotFoundException('File tidak ditemukan');
        }

        return [
            'path' => storage_path('app/'.$path),
            'filename' => basename($dto->file),
        ];
    }

    public function show(FileAccessDTO $dto): array
    {
        $path = 'public/'.$dto->file;

        if (! Storage::exists($path)) {
            throw new EntityNotFoundException('File tidak ditemukan');
        }

        return [
            'path' => storage_path("app/{$path}"),
            'mime_type' => Storage::mimeType($path),
            'filename' => basename($dto->file),
            'download' => $dto->download,
        ];
    }

    public function lockUploadedPdf(LockFileDTO $dto): array
    {
        $uploadedFile = $dto->file;
        $inputPath = $uploadedFile->getRealPath();
        $outputPath = storage_path('app/public/locked_'.time().'.pdf');

        $pdf = new MyTcpdfFpdi;

        $pageCount = $pdf->setSourceFile($inputPath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $tplIdx = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx);
        }

        $password = config('file.lock_password');
        $pdf->SetProtection(['print'], $password, null, 0, null);
        $pdf->Output($outputPath, 'F');

        $publicPath = str_replace(storage_path('app/public'), 'storage', $outputPath);
        $relativePath = str_replace(storage_path('app/public').'/', '', $outputPath);

        return [
            'url' => url($publicPath),
            'path' => $relativePath,
        ];
    }

    public function lockUploadedImage(LockFileDTO $dto): array
    {
        $uploadedFile = $dto->file;
        $imagePath = $uploadedFile->getRealPath();

        $outputPath = storage_path('app/public/locked_img_'.time().'.pdf');

        $pdf = new TCPDF;
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $pdf->Image($imagePath, 15, 20, 180, 160);

        $password = config('file.lock_password');
        $pdf->SetProtection(['print', 'copy'], $password, null, 0, null);
        $pdf->Output($outputPath, 'F');

        $publicPath = str_replace(storage_path('app/public'), 'storage', $outputPath);
        $relativePath = str_replace(storage_path('app/public').'/', '', $outputPath);

        return [
            'url' => url($publicPath),
            'path' => $relativePath,
        ];
    }

    public function getFileBase64(FileAccessDTO $dto): array
    {
        $path = 'public/'.$dto->file;

        if (! Storage::exists($path)) {
            throw new EntityNotFoundException('File tidak ditemukan');
        }

        $fileContent = Storage::get($path);
        $mimeType = Storage::mimeType($path);

        $base64 = 'data:'.$mimeType.';base64,'.base64_encode($fileContent);

        return [
            'path' => $path,
            'mime_type' => $mimeType,
            'base64' => $base64,
        ];
    }
}
