<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\CloudinaryService;

class CloudinaryServiceTest extends TestCase
{
    public function testIsConfiguredReadsEnvironmentVariables(): void
    {
        // Based on .env configuration
        $this->assertTrue(CloudinaryService::isConfigured());
        $this->assertEquals('st16osno', CloudinaryService::getCloudName());
        $this->assertEquals('358155671526412', CloudinaryService::getApiKey());
        $this->assertEquals('NovaFlow_image', CloudinaryService::getDefaultFolder());
    }

    public function testUrlResolutionWithExternalHttpUrl(): void
    {
        $externalUrl = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff';
        $resolved = CloudinaryService::url($externalUrl);
        $this->assertEquals($externalUrl, $resolved);
    }

    public function testUrlResolutionWithEmptyFallback(): void
    {
        $fallback = 'https://example.com/placeholder.jpg';
        $resolved = CloudinaryService::url('', [], $fallback);
        $this->assertEquals($fallback, $resolved);
    }

    public function testTransformUrlInjectsTransformationsIntoCloudinaryUrl(): void
    {
        $rawCloudinaryUrl = 'https://res.cloudinary.com/st16osno/image/upload/v1700000000/NovaFlow_image/sample.jpg';
        
        $transformed = CloudinaryService::url($rawCloudinaryUrl, [
            'width' => 400,
            'height' => 300,
            'crop' => 'fill',
            'quality' => 'auto',
            'format' => 'auto'
        ]);

        $this->assertStringContainsString('c_fill', $transformed);
        $this->assertStringContainsString('f_auto', $transformed);
        $this->assertStringContainsString('h_300', $transformed);
        $this->assertStringContainsString('q_auto', $transformed);
        $this->assertStringContainsString('w_400', $transformed);
        $this->assertStringContainsString('/image/upload/c_fill,f_auto,h_300,q_auto,w_400/v1700000000/NovaFlow_image/sample.jpg', $transformed);
    }

    public function testClassAliasIsAvailable(): void
    {
        $this->assertTrue(class_exists(\NovaFlow\Core\CloudinaryService::class));
    }
}
