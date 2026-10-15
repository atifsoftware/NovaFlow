<?php

namespace Tests\Unit\Storage;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\UploadHandler;

class UploadSecurityTest extends TestCase
{
    private string $tempUploadDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempUploadDir = sys_get_temp_dir() . '/novaflow_test_uploads_' . uniqid() . '/';
        if (!is_dir($this->tempUploadDir)) {
            mkdir($this->tempUploadDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up temp dir
        if (is_dir($this->tempUploadDir)) {
            $files = glob($this->tempUploadDir . '*');
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }
            rmdir($this->tempUploadDir);
        }
        parent::tearDown();
    }

    public function testFakeExtensionPolyglotRejectedByRealMimeCheck(): void
    {
        $uploader = new UploadHandler($this->tempUploadDir);
        $uploader->allowedTypes(['image/png', 'image/jpeg']);

        // Create a temporary file containing PHP code but named with .png extension
        $tmpFakeImage = tempnam(sys_get_temp_dir(), 'fake_img');
        file_put_contents($tmpFakeImage, '<?php echo "evil_payload"; ?>');

        $_FILES['avatar'] = [
            'name' => 'fake_avatar.png',
            'type' => 'image/png', // Fake MIME sent by client header
            'tmp_name' => $tmpFakeImage,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFakeImage)
        ];

        $res = $uploader->upload('avatar');

        $this->assertFalse($res['success']);
        $this->assertEquals('File type not allowed', $res['message']);

        @unlink($tmpFakeImage);
    }

    public function testFileSizeLimitEnforcement(): void
    {
        $uploader = new UploadHandler($this->tempUploadDir);
        // Set maximum allowed size to 100 bytes
        $uploader->maxSize(100);

        $tmpFile = tempnam(sys_get_temp_dir(), 'big_file');
        file_put_contents($tmpFile, "%PDF-1.4 \n" . str_repeat('A', 500)); // > 100 bytes valid PDF

        $_FILES['doc'] = [
            'name' => 'document.pdf',
            'type' => 'application/pdf',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => 500
        ];

        $res = $uploader->upload('doc');
        $this->assertFalse($res['success']);
        $this->assertEquals('File size exceeds limit', $res['message']);

        @unlink($tmpFile);
    }

    public function testDisallowedFileExtensionRejection(): void
    {
        $uploader = new UploadHandler($this->tempUploadDir);
        $uploader->allowedExtensions(['jpg', 'jpeg']);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_ext');
        file_put_contents($tmpFile, '%PDF-1.4 sample pdf content');

        $_FILES['file'] = [
            'name' => 'statement.pdf',
            'type' => 'application/pdf',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFile)
        ];

        $res = $uploader->upload('file');
        $this->assertFalse($res['success']);
        $this->assertEquals('File extension not allowed', $res['message']);

        @unlink($tmpFile);
    }

    public function testDeletePathTraversalCannotEscapeUploadDirectory(): void
    {
        // Create a critical file outside the upload directory
        $parentDir = dirname($this->tempUploadDir);
        $sensitiveFile = $parentDir . '/system_critical_' . uniqid() . '.txt';
        file_put_contents($sensitiveFile, 'IMPORTANT_SERVER_DATA');

        $this->assertFileExists($sensitiveFile);

        $uploader = new UploadHandler($this->tempUploadDir);

        // Attempting to delete using ../ relative paths
        $attackPath = '../' . basename($sensitiveFile);
        $this->assertFalse($uploader->delete($attackPath));

        // The sensitive file MUST still exist!
        $this->assertFileExists($sensitiveFile);

        @unlink($sensitiveFile);
    }
}
