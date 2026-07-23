<?php

namespace App\Services;

use App\Helpers\File;
use Illuminate\Http\Request;
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

    public function upload(Request $request)
    {
        $uploadedFile = $request->file('file');

        if (! $uploadedFile || ! $uploadedFile->isValid()) {
            throw new \Exception('File tidak valid atau tidak ditemukan');
        }

        $maxFileSize = $this->getMaxFileSize();
        $maxSizeInBytes = $maxFileSize * 1024 * 1024;
        if ($uploadedFile->getSize() > $maxSizeInBytes) {
            throw new \Exception('Ukuran file tidak boleh lebih dari '.$maxFileSize.' MB');
        }

        $path = $request->filled('path') ? $request->path : 'uploads';

        $filename = File::generateFilename($uploadedFile->getClientOriginalName());
        $relativePath = $uploadedFile->storeAs($path, $filename, 'public');
        $publicPath = Storage::url($relativePath);

        $url = url($publicPath);
        $baseUrlBefore = env('BASE_URL_BEFORE');
        $baseUrlAfter = env('BASE_URL_AFTER');
        $newUrl = str_replace($baseUrlBefore, $baseUrlAfter, $url);

        return [
            'url' => $newUrl,
            'path' => $relativePath,
        ];
    }

    public function compressImage(Request $request)
    {
        $image = $request->file('image');

        if (! $image || strpos($image->getMimeType(), 'image') === false) {
            throw new \Exception('Tidak ada gambar atau file tidak valid');
        }

        $imageName = File::generateFilename($image->getClientOriginalName());
        $imageExtension = $image->getClientOriginalExtension();

        $pathFolder = $request->filled('path') ? $request->path : 'compressed_images';
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
        $baseUrlBefore = env('BASE_URL_BEFORE');
        $baseUrlAfter = env('BASE_URL_AFTER');
        $newUrl = str_replace($baseUrlBefore, $baseUrlAfter, $url);

        return [
            'url' => $newUrl,
            'path' => $relativePath,
        ];
    }

    public function download(Request $request)
    {
        $file = $request->file;
        $path = 'public/'.$file;

        if (! Storage::exists($path)) {
            throw new \Exception('File tidak ditemukan', 404);
        }

        return response()->download(storage_path('app/'.$path));
    }

    public function show(Request $request)
    {
        $file = $request->file;
        $path = 'public/'.$file;

        if (! Storage::exists($path)) {
            throw new \Exception('File tidak ditemukan', 404);
        }

        $absolutePath = storage_path("app/{$path}");
        $mimeType = Storage::mimeType($path);

        if ($request->boolean('download')) {
            return response()->download($absolutePath, basename($absolutePath), [
                'Content-Type' => $mimeType,
            ]);
        }

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.basename($absolutePath).'"',
        ]);
    }

    public function lockUploadedPdf(Request $request)
    {
        $uploadedFile = $request->file('file');
        $inputPath = $uploadedFile->getRealPath();
        $outputPath = storage_path('app/public/locked_'.time().'.pdf');

        $pdf = new MyTcpdfFpdi;

        $pageCount = $pdf->setSourceFile($inputPath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $tplIdx = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx);
        }

        $pdf->SetProtection(['print'], 'secret123', null, 0, null);
        $pdf->Output($outputPath, 'F');

        $publicPath = str_replace(storage_path('app/public'), 'storage', $outputPath);
        $relativePath = str_replace(storage_path('app/public').'/', '', $outputPath);

        return [
            'url' => url($publicPath),
            'path' => $relativePath,
        ];
    }

    public function lockUploadedImage(Request $request)
    {
        $uploadedFile = $request->file('file');
        $imagePath = $uploadedFile->getRealPath();

        $outputPath = storage_path('app/public/locked_img_'.time().'.pdf');

        $pdf = new TCPDF;
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $pdf->Image($imagePath, 15, 20, 180, 160);

        $pdf->SetProtection(['print', 'copy'], 'secret123', null, 0, null);
        $pdf->Output($outputPath, 'F');

        $publicPath = str_replace(storage_path('app/public'), 'storage', $outputPath);
        $relativePath = str_replace(storage_path('app/public').'/', '', $outputPath);

        return [
            'url' => url($publicPath),
            'path' => $relativePath,
        ];
    }

    public function getFileBase64(Request $request)
    {
        $path = 'public/'.$request->file;

        if (! Storage::exists($path)) {
            throw new \Exception('File tidak ditemukan', 404);
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
