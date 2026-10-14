<?php

namespace Tests\Unit\Storage;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Storage;
use NovaFlow\Core\Storage\StorageManager;
use NovaFlow\Core\Storage\Drivers\LocalStorageDriver;
use NovaFlow\Core\Storage\Drivers\S3StorageDriver;

class StorageTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/novaflow_test_storage_' . uniqid();
        mkdir($this->tempDir, 0755, true);

        $manager = new StorageManager([
            'default' => 'test_local',
            'disks' => [
                'test_local' => [
                    'driver' => 'local',
                    'root'   => $this->tempDir,
                    'url'    => 'http://localhost/test-storage'
                ],
                'test_s3' => [
                    'driver' => 's3',
                    'key'    => 'test_key',
                    'secret' => 'test_secret',
                    'region' => 'eu-west-1',
                    'bucket' => 'my-test-bucket',
                ]
            ]
        ]);

        Storage::setManager($manager);
    }

    protected function tearDown(): void
    {
        Storage::setManager(null);
        // Clean up temp directory
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    public function testPutAndGet()
    {
        $this->assertTrue(Storage::put('greeting.txt', 'Hello NovaFlow!'));
        $this->assertTrue(Storage::exists('greeting.txt'));
        $this->assertFalse(Storage::missing('greeting.txt'));

        $this->assertEquals('Hello NovaFlow!', Storage::get('greeting.txt'));
    }

    public function testFileSizeAndLastModified()
    {
        Storage::put('info.txt', '12345');
        $this->assertEquals(5, Storage::size('info.txt'));
        $this->assertGreaterThan(0, Storage::lastModified('info.txt'));
    }

    public function testCopyAndMove()
    {
        Storage::put('source.txt', 'Original text');

        $this->assertTrue(Storage::copy('source.txt', 'backup.txt'));
        $this->assertTrue(Storage::exists('source.txt'));
        $this->assertTrue(Storage::exists('backup.txt'));
        $this->assertEquals('Original text', Storage::get('backup.txt'));

        $this->assertTrue(Storage::move('source.txt', 'destination.txt'));
        $this->assertFalse(Storage::exists('source.txt'));
        $this->assertTrue(Storage::exists('destination.txt'));
    }

    public function testAppendAndPrepend()
    {
        Storage::put('log.txt', 'Middle');
        Storage::append('log.txt', ' End');
        Storage::prepend('log.txt', 'Start ');

        $this->assertEquals('Start Middle End', Storage::get('log.txt'));
    }

    public function testDelete()
    {
        Storage::put('file_to_delete.txt', 'content');
        $this->assertTrue(Storage::exists('file_to_delete.txt'));

        $this->assertTrue(Storage::delete('file_to_delete.txt'));
        $this->assertFalse(Storage::exists('file_to_delete.txt'));
    }

    public function testUrlGeneration()
    {
        $url = Storage::url('avatars/user_1.png');
        $this->assertEquals('http://localhost/test-storage/avatars/user_1.png', $url);
    }

    public function testNestedDirectoryCreation()
    {
        Storage::put('deep/nested/folder/document.pdf', 'PDF Data');
        $this->assertTrue(Storage::exists('deep/nested/folder/document.pdf'));
        $this->assertEquals('PDF Data', Storage::get('deep/nested/folder/document.pdf'));

        $files = Storage::files('deep', true);
        $this->assertCount(1, $files);
    }

    public function testS3DriverInstantiationAndUrl()
    {
        $s3 = Storage::disk('test_s3');
        $this->assertInstanceOf(S3StorageDriver::class, $s3);

        $url = $s3->url('uploads/image.jpg');
        $this->assertEquals('https://my-test-bucket.s3.eu-west-1.amazonaws.com/uploads/image.jpg', $url);
    }
}
