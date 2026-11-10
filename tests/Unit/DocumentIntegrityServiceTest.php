<?php

namespace Tests\Unit;

use App\Models\ApplicationDocument;
use App\Services\DocumentIntegrityService;
use App\Services\SupabaseStorageService;
use Tests\TestCase;

class DocumentIntegrityServiceTest extends TestCase
{
    public function test_calculate_file_hash_generates_valid_64_char_sha256(): void
    {
        $mockStorage = $this->createMock(SupabaseStorageService::class);
        $service = new DocumentIntegrityService($mockStorage);

        // Buat file temporary
        $tmp = tempnam(sys_get_temp_dir(), 'test_cv_');
        $content = '%PDF-1.4 Test Curriculum Vitae Content for Cryptography Research';
        file_put_contents($tmp, $content);

        $hash = $service->calculateFileHash($tmp);
        unlink($tmp);

        $this->assertEquals(64, strlen($hash));
        $this->assertEquals(hash('sha256', $content), $hash);
    }

    public function test_integrity_verification_returns_valid_when_cloud_file_is_identical(): void
    {
        $content = '%PDF-1.4 Original Unmodified CV Content';
        $originalHash = hash('sha256', $content);

        $mockStorage = $this->createMock(SupabaseStorageService::class);
        $mockStorage->expects($this->once())
            ->method('download')
            ->with('cv/1/cv.pdf')
            ->willReturn($content);

        $service = new DocumentIntegrityService($mockStorage);

        $doc = new ApplicationDocument([
            'id' => 1,
            'storage_path' => 'cv/1/cv.pdf',
            'original_sha256' => $originalHash,
        ]);

        $result = $service->verifyIntegrity($doc);

        $this->assertTrue($result['is_valid']);
        $this->assertEquals('VALID', $result['status']);
        $this->assertEquals($originalHash, $result['original_hash']);
        $this->assertEquals($originalHash, $result['current_hash']);
    }

    public function test_integrity_verification_returns_tampered_when_cloud_file_is_modified(): void
    {
        $originalContent = '%PDF-1.4 Original CV Content';
        $originalHash = hash('sha256', $originalContent);

        // Simulasi perubahan byte pada file cloud
        $tamperedContent = '%PDF-1.4 Tampered CV Content with altered bytes!';
        $tamperedHash = hash('sha256', $tamperedContent);

        $mockStorage = $this->createMock(SupabaseStorageService::class);
        $mockStorage->expects($this->once())
            ->method('download')
            ->with('cv/1/cv.pdf')
            ->willReturn($tamperedContent);

        $service = new DocumentIntegrityService($mockStorage);

        $doc = new ApplicationDocument([
            'id' => 1,
            'storage_path' => 'cv/1/cv.pdf',
            'original_sha256' => $originalHash,
        ]);

        $result = $service->verifyIntegrity($doc);

        $this->assertFalse($result['is_valid']);
        $this->assertEquals('TAMPERED', $result['status']);
        $this->assertEquals($originalHash, $result['original_hash']);
        $this->assertEquals($tamperedHash, $result['current_hash']);
        $this->assertNotEquals($result['original_hash'], $result['current_hash']);
    }
}
