<?php

namespace App\Services;

use App\Models\ApplicationDocument;
use Exception;
use Illuminate\Support\Facades\Log;

class DocumentIntegrityService
{
    public function __construct(
        protected SupabaseStorageService $storageService
    ) {}

    /**
     * Hitung SHA-256 dari file lokal (misal saat upload).
     *
     * @return string 64-karakter hex hash
     *
     * @throws Exception
     */
    public function calculateFileHash(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new Exception("File tidak ditemukan: {$filePath}");
        }

        $hash = hash_file('sha256', $filePath);
        if ($hash === false) {
            throw new Exception("Gagal menghitung SHA-256 dari file: {$filePath}");
        }

        return $hash;
    }

    /**
     * Hitung SHA-256 dari string/binary content.
     */
    public function calculateContentHash(string $content): string
    {
        return hash('sha256', $content);
    }

    /**
     * Verifikasi integritas dokumen dari Supabase Storage terhadap hash asli di database.
     *
     * Alur:
     * 1. Ambil storage_path dari record dokumen di database
     * 2. Unduh konten file dari Supabase Storage (private bucket)
     * 3. Hitung SHA-256 dari file yang baru diunduh
     * 4. Bandingkan secara aman menggunakan hash_equals() terhadap original_sha256
     * 5. Return status: VALID jika identik, TAMPERED jika berbeda
     *
     * @return array{
     *     is_valid: bool,
     *     status: string,
     *     original_hash: string,
     *     current_hash: string,
     *     file_size_bytes: int,
     *     verified_at: string,
     *     notes: string
     * }
     *
     * @throws Exception
     */
    public function verifyIntegrity(ApplicationDocument $document): array
    {
        // 1. Ambil file dari Supabase Storage
        $fileContent = $this->storageService->download($document->storage_path);

        // 2. Hitung SHA-256 saat ini
        $currentHash = $this->calculateContentHash($fileContent);

        // 3. Bandingkan dengan hash asli yang tersimpan di PostgreSQL
        $isValid = hash_equals($document->original_sha256, $currentHash);

        $status = $isValid ? 'VALID' : 'TAMPERED';
        $notes = $isValid
            ? 'Integritas dokumen terjamin. Nilai SHA-256 file di cloud identik dengan dokumen asli saat diunggah.'
            : 'Peringatan! Terdeteksi anomali atau manipulasi data. Nilai SHA-256 dokumen di cloud tidak cocok dengan hash asli!';

        Log::info("Verifikasi Integritas SHA-256 dokumen ID: {$document->id}", [
            'status' => $status,
            'original_sha256' => $document->original_sha256,
            'current_sha256' => $currentHash,
            'is_valid' => $isValid,
        ]);

        return [
            'is_valid' => $isValid,
            'status' => $status,
            'original_hash' => $document->original_sha256,
            'current_hash' => $currentHash,
            'file_size_bytes' => strlen($fileContent),
            'verified_at' => now()->translatedFormat('d F Y H:i:s T'),
            'notes' => $notes,
        ];
    }
}
