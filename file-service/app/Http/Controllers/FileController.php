<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\File;
use Intervention\Image\Facades\Image;
use App\Rules\SafeFileExtension;
use Illuminate\Http\Request;
use App\Services\MyTcpdfFpdi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class FileController extends Controller
{
    CONST DEFAULT_MAX_FILE_SIZE = 10; // in MB

    protected function getMaxFileSize(): int
    {
        return (int) DB::table('site_settings')
            ->value('max_file_size') 
            ?? self::DEFAULT_MAX_FILE_SIZE;
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => ['required', 'file', new SafeFileExtension]
            ]);

            $uploadedFile = $request->file('file');

            if (!$uploadedFile || !$uploadedFile->isValid()) {
                return ApiResponse::error('File tidak valid atau tidak ditemukan');
            }

            $maxFileSize = $this->getMaxFileSize();
            $maxSizeInBytes = $maxFileSize * 1024 * 1024;
            if ($uploadedFile->getSize() > $maxSizeInBytes) {
                return ApiResponse::error('Ukuran file tidak boleh lebih dari ' . $maxFileSize . ' MB');
            }
            
            $path = $request->filled('path') ? $request->path : "uploads";

            $filename = File::generateFilename($uploadedFile->getClientOriginalName());
            $relativePath = $uploadedFile->storeAs($path, $filename, 'public');
            $publicPath = Storage::url($relativePath);

            $url = url($publicPath);
            $baseUrlBefore = env('BASE_URL_BEFORE');
            $baseUrlAfter = env('BASE_URL_AFTER');
            $newUrl = str_replace($baseUrlBefore, $baseUrlAfter, $url);

            return ApiResponse::success([
                'url'  => $newUrl,
                'path' => $relativePath
            ], 'Berhasil menyimpan file', 201);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function compressImage(Request $request)
    {
        try {
            $request->validate([
                'image' => ['required', 'file', new SafeFileExtension]
            ]);

            $image = $request->file('image');

            if (!$image || strpos($image->getMimeType(), 'image') === false) {
                return ApiResponse::error("Tidak ada gambar atau file tidak valid");
            }

            $imageName = File::generateFilename($image->getClientOriginalName());
            $imageExtension = $image->getClientOriginalExtension();

            $pathFolder = $request->filled('path') ? $request->path : "compressed_images";
            $relativePath = $image->storeAs($pathFolder, $imageName, 'public');

            $widthLimit = 1920;
            $heightLimit = 1080;
            $maxFileSize = 2 * 1024 * 1024; // Compress if > 2 MB

            $compressedImage = Image::make($image);
            
            if ($compressedImage->width() > $widthLimit || $compressedImage->height() > $heightLimit) {
                // Compress the image
                $compressedImage->resize(1920, 1080, function ($constraint) {
                    $constraint->aspectRatio(); // Maintain aspect ratio
                    $constraint->upsize(); // Prevent upsizing
                });
            }
            if ($compressedImage->filesize() > $maxFileSize) { // 2 MB in bytes
                // Compress and encode the image
                $compressedImage->encode($imageExtension, 50);
            }
            Storage::disk('public')->put($relativePath, $compressedImage->stream());
            $url = url(Storage::url($relativePath));
            $baseUrlBefore = env('BASE_URL_BEFORE');
            $baseUrlAfter = env('BASE_URL_AFTER');
            $newUrl = str_replace($baseUrlBefore, $baseUrlAfter, $url);
            
            return ApiResponse::success([
                'url' => $newUrl,
                'path' => $relativePath
            ], 'Berhasil melakukan kompresi gambar', 201);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function download(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|string'
            ]);

            $file = $request->file;
            $path = 'public/' . $file;

            if (!Storage::exists($path)) {
                return ApiResponse::error('File tidak ditemukan', null, 404);
            }

            return response()->download(storage_path('app/' . $path));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function show(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|string'
            ]);
    
            $file = $request->file;
            $path = "public/" . $file;
    
            if (!Storage::exists($path)) {
                return ApiResponse::error('File tidak ditemukan', null, 404);
            }
    
            $absolutePath = storage_path("app/{$path}");
            $mimeType = Storage::mimeType($path);
    
            if ($request->boolean('download')) {
                return response()->download($absolutePath, basename($absolutePath), [
                    'Content-Type' => $mimeType,
                ]);
            }
    
            return response()->file($absolutePath, [
                'Content-Type'        => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"'
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function lockUploadedPdf(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf|max:20480',
            ]);
    
            $uploadedFile = $request->file('file');
            $inputPath = $uploadedFile->getRealPath();
            $outputPath = storage_path('app/public/locked_' . time() . '.pdf');
    
            $pdf = new MyTcpdfFpdi();
    
            $pageCount = $pdf->setSourceFile($inputPath);
    
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplIdx = $pdf->importPage($i);
                $pdf->AddPage();
                $pdf->useTemplate($tplIdx);
            }
    
            // lock PDF
            $pdf->SetProtection(['print'], 'secret123', null, 0, null);
    
            $pdf->Output($outputPath, 'F');
    
            $publicPath = str_replace(storage_path('app/public'), 'storage', $outputPath);
            $relativePath = str_replace(storage_path('app/public') . '/', '', $outputPath);
    
            return ApiResponse::success([
                'url' => url($publicPath),
                'path' => $relativePath
            ], 'Berhasil menambahkan password pada file PDF');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function lockUploadedImage(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480',
            ]);
    
            $uploadedFile = $request->file('file');
            $imagePath = $uploadedFile->getRealPath();
    
            $outputPath = storage_path('app/public/locked_img_' . time() . '.pdf');
    
            $pdf = new TCPDF();
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->AddPage();
    
            $pdf->Image($imagePath, 15, 20, 180, 160);
    
            // lock PDF
            $pdf->SetProtection(['print', 'copy'], 'secret123', null, 0, null);
    
            $pdf->Output($outputPath, 'F');
    
            $publicPath = str_replace(storage_path('app/public'), 'storage', $outputPath);
            $relativePath = str_replace(storage_path('app/public') . '/', '', $outputPath);
    
            return ApiResponse::success([
                'url' => url($publicPath),
                'path' => $relativePath
            ], 'Berhasil mengonversi gambar ke PDF dan menambahkan password pada file PDF');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getFileBase64(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|string'
            ]);
    
            $path = 'public/' . $request->file;
    
            if (!Storage::exists($path)) {
                return ApiResponse::error('File tidak ditemukan', null, 404);
            }
    
            $fileContent = Storage::get($path);
    
            $mimeType = Storage::mimeType($path);
    
            // encode base64
            $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileContent);
    
            return ApiResponse::success([
                'path' => $path,
                'mime_type' => $mimeType,
                'base64' => $base64
            ], 'Berhasil mengonversi file ke base64');
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
