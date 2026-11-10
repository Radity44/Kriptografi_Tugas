<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorageService
{
    protected string $url;

    protected string $key;

    protected string $bucket;

    public function __construct()
    {
        $this->url = rtrim((string) config('services.supabase.url', env('SUPABASE_URL', '')), '/');
        $this->key = (string) config('services.supabase.key', env('SUPABASE_KEY', ''));
        $this->bucket = (string) config('services.supabase.bucket', env('SUPABASE_BUCKET', 'cv-mahasiswa'));
    }

    /**
     * Upload a file to Supabase Storage.
     *
     * @param  string  $storagePath  Target path inside the bucket (e.g. cv/1/abc.pdf)
     * @param  string  $localFilePath  Absolute path to local file
     * @param  string  $mimeType  Mime type (e.g. application/pdf)
     *
     * @throws Exception
     */
    public function upload(string $storagePath, string $localFilePath, string $mimeType = 'application/pdf'): array
    {
        if (empty($this->url) || empty($this->key)) {
            throw new Exception('Konfigurasi Supabase Storage belum lengkap di file .env (SUPABASE_URL / SUPABASE_KEY).');
        }

        if (! file_exists($localFilePath)) {
            throw new Exception("File lokal tidak ditemukan: {$localFilePath}");
        }

        $endpoint = "{$this->url}/storage/v1/object/{$this->bucket}/".ltrim($storagePath, '/');
        $fileStream = fopen($localFilePath, 'r');

        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => "Bearer {$this->key}",
            'Content-Type' => $mimeType,
            'x-upsert' => 'true',
        ])->withBody($fileStream, $mimeType)->post($endpoint);

        if (is_resource($fileStream)) {
            fclose($fileStream);
        }

        if (! $response->successful()) {
            Log::error('Supabase Storage upload failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'path' => $storagePath,
            ]);
            throw new Exception('Gagal mengunggah file ke Supabase Storage: '.$response->body());
        }

        return $response->json() ?? ['Key' => "{$this->bucket}/{$storagePath}"];
    }

    /**
     * Download/fetch file raw content from Supabase Storage private bucket.
     *
     * @return string Raw file binary content
     *
     * @throws Exception
     */
    public function download(string $storagePath): string
    {
        if (empty($this->url) || empty($this->key)) {
            throw new Exception('Konfigurasi Supabase Storage belum lengkap di file .env (SUPABASE_URL / SUPABASE_KEY).');
        }

        $endpoint = "{$this->url}/storage/v1/object/authenticated/{$this->bucket}/".ltrim($storagePath, '/');

        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => "Bearer {$this->key}",
        ])->get($endpoint);

        if (! $response->successful()) {
            Log::error('Supabase Storage download failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'path' => $storagePath,
            ]);
            throw new Exception("Gagal mengunduh file dari Supabase Storage (HTTP {$response->status()}).");
        }

        return $response->body();
    }

    /**
     * Create a signed URL for secure temporary file viewing/downloading.
     *
     * @param  int  $expiresIn  Seconds valid (default 3600 = 1 hour)
     * @return string Signed download URL
     *
     * @throws Exception
     */
    public function createSignedUrl(string $storagePath, int $expiresIn = 3600): string
    {
        if (empty($this->url) || empty($this->key)) {
            throw new Exception('Konfigurasi Supabase Storage belum lengkap di file .env.');
        }

        $endpoint = "{$this->url}/storage/v1/object/sign/{$this->bucket}/".ltrim($storagePath, '/');

        $response = Http::withHeaders([
            'apikey' => $this->key,
            'Authorization' => "Bearer {$this->key}",
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'expiresIn' => $expiresIn,
        ]);

        if (! $response->successful()) {
            Log::error('Supabase Storage sign URL failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('Gagal membuat Signed URL Supabase: '.$response->body());
        }

        $signedPath = $response->json('signedURL');

        return "{$this->url}/storage/v1{$signedPath}";
    }
}
