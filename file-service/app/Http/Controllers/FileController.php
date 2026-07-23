<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\FileDownloadRequest;
use App\Http\Requests\LockImageRequest;
use App\Http\Requests\LockPdfRequest;
use App\Http\Requests\UploadFileRequest;
use App\Services\FileService;

class FileController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    public function upload(UploadFileRequest $request)
    {
        try {
            $result = $this->fileService->upload($request);

            return ApiResponse::success($result, 'Berhasil menyimpan file', 201);
        } catch (\Exception $e) {
            if ($e->getMessage() && ! $e->getCode()) {
                return ApiResponse::error($e->getMessage());
            }
            throw $e;
        }
    }

    public function compressImage(UploadFileRequest $request)
    {
        try {
            $result = $this->fileService->compressImage($request);

            return ApiResponse::success($result, 'Berhasil melakukan kompresi gambar', 201);
        } catch (\Exception $e) {
            if ($e->getMessage() && ! $e->getCode()) {
                return ApiResponse::error($e->getMessage());
            }
            throw $e;
        }
    }

    public function download(FileDownloadRequest $request)
    {
        try {
            return $this->fileService->download($request);
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                return ApiResponse::error($e->getMessage(), null, 404);
            }
            throw $e;
        }
    }

    public function show(FileDownloadRequest $request)
    {
        try {
            return $this->fileService->show($request);
        } catch (\Throwable $th) {
            if ($th->getCode() == 404) {
                return ApiResponse::error($th->getMessage(), null, 404);
            }
            throw $th;
        }
    }

    public function lockUploadedPdf(LockPdfRequest $request)
    {
        try {
            $result = $this->fileService->lockUploadedPdf($request);

            return ApiResponse::success($result, 'Berhasil menambahkan password pada file PDF');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function lockUploadedImage(LockImageRequest $request)
    {
        try {
            $result = $this->fileService->lockUploadedImage($request);

            return ApiResponse::success($result, 'Berhasil mengonversi gambar ke PDF dan menambahkan password pada file PDF');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getFileBase64(FileDownloadRequest $request)
    {
        try {
            $result = $this->fileService->getFileBase64($request);

            return ApiResponse::success($result, 'Berhasil mengonversi file ke base64');
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                return ApiResponse::error($e->getMessage(), null, 404);
            }
            throw $e;
        }
    }
}
