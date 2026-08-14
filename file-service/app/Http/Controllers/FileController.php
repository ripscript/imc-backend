<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\FileDownloadRequest;
use App\Http\Requests\LockImageRequest;
use App\Http\Requests\LockPdfRequest;
use App\Http\Requests\UploadFileRequest;
use App\Services\DTOs\CompressImageDTO;
use App\Services\DTOs\FileAccessDTO;
use App\Services\DTOs\LockFileDTO;
use App\Services\DTOs\UploadFileDTO;
use App\Services\FileService;

class FileController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    public function upload(UploadFileRequest $request)
    {
        $dto = new UploadFileDTO($request->validated());
        $result = $this->fileService->upload($dto);

        return ApiResponse::success($result, 'Berhasil menyimpan file', 201);
    }

    public function compressImage(UploadFileRequest $request)
    {
        $dto = new CompressImageDTO($request->validated());
        $result = $this->fileService->compressImage($dto);

        return ApiResponse::success($result, 'Berhasil melakukan kompresi gambar', 201);
    }

    public function download(FileDownloadRequest $request)
    {
        $dto = new FileAccessDTO($request->validated());
        $result = $this->fileService->download($dto);

        return response()->download($result['path'], $result['filename']);
    }

    public function show(FileDownloadRequest $request)
    {
        $dto = new FileAccessDTO($request->validated());
        $result = $this->fileService->show($dto);

        if ($result['download']) {
            return response()->download($result['path'], $result['filename'], [
                'Content-Type' => $result['mime_type'],
            ]);
        }

        return response()->file($result['path'], [
            'Content-Type' => $result['mime_type'],
            'Content-Disposition' => 'inline; filename="'.$result['filename'].'"',
        ]);
    }

    public function lockUploadedPdf(LockPdfRequest $request)
    {
        $dto = new LockFileDTO($request->validated());
        $result = $this->fileService->lockUploadedPdf($dto);

        return ApiResponse::success($result, 'Berhasil menambahkan password pada file PDF');
    }

    public function lockUploadedImage(LockImageRequest $request)
    {
        $dto = new LockFileDTO($request->validated());
        $result = $this->fileService->lockUploadedImage($dto);

        return ApiResponse::success($result, 'Berhasil mengonversi gambar ke PDF dan menambahkan password pada file PDF');
    }

    public function getFileBase64(FileDownloadRequest $request)
    {
        $dto = new FileAccessDTO($request->validated());
        $result = $this->fileService->getFileBase64($dto);

        return ApiResponse::success($result, 'Berhasil mengonversi file ke base64');
    }
}
