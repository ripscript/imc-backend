<?php

namespace Tests\Unit;

use App\Services\FileService;
use Tests\TestCase;

class FileServiceTest extends TestCase
{
    public function test_can_instantiate_service()
    {
        $service = app()->make(FileService::class);
        $this->assertInstanceOf(FileService::class, $service);
    }

    public function test_service_has_upload_method()
    {
        $service = new FileService();
        $this->assertTrue(method_exists($service, 'upload'));
    }

    public function test_service_has_compress_image_method()
    {
        $service = new FileService();
        $this->assertTrue(method_exists($service, 'compressImage'));
    }

    public function test_service_has_download_method()
    {
        $service = new FileService();
        $this->assertTrue(method_exists($service, 'download'));
    }

    public function test_service_has_show_method()
    {
        $service = new FileService();
        $this->assertTrue(method_exists($service, 'show'));
    }

    public function test_service_has_lock_uploaded_pdf_method()
    {
        $service = new FileService();
        $this->assertTrue(method_exists($service, 'lockUploadedPdf'));
    }

    public function test_service_has_lock_uploaded_image_method()
    {
        $service = new FileService();
        $this->assertTrue(method_exists($service, 'lockUploadedImage'));
    }

    public function test_service_has_get_file_base64_method()
    {
        $service = new FileService();
        $this->assertTrue(method_exists($service, 'getFileBase64'));
    }

    public function test_default_max_file_size_constant_is_defined()
    {
        $this->assertEquals(10, FileService::DEFAULT_MAX_FILE_SIZE);
    }
}
