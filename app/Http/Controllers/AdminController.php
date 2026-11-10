<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Models\InternshipApplication;
use App\Services\DocumentIntegrityService;
use App\Services\SupabaseStorageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    public function __construct(
        protected SupabaseStorageService $storageService,
        protected DocumentIntegrityService $integrityService
    ) {}

    public function dashboard(): View
    {
        $stats = [
            'total' => InternshipApplication::count(),
            'menunggu' => InternshipApplication::where('status', InternshipApplication::STATUS_MENUNGGU)->count(),
            'diterima' => InternshipApplication::where('status', InternshipApplication::STATUS_DITERIMA)->count(),
            'ditolak' => InternshipApplication::where('status', InternshipApplication::STATUS_DITOLAK)->count(),
            'total_documents' => ApplicationDocument::count(),
        ];

        $recentApplications = InternshipApplication::with(['user', 'document'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentApplications'));
    }

    public function index(Request $request): View
    {
        $query = InternshipApplication::with(['user', 'document', 'verifier']);

        if ($request->filled('status') && in_array($request->status, [
            InternshipApplication::STATUS_MENUNGGU,
            InternshipApplication::STATUS_DITERIMA,
            InternshipApplication::STATUS_DITOLAK,
        ])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'ilike', "%{$search}%")
                    ->orWhere('internship_position', 'ilike', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'ilike', "%{$search}%")
                           ->orWhere('email', 'ilike', "%{$search}%");
                    });
            });
        }

        $applications = $query->latest()->paginate(10)->withQueryString();

        return view('admin.pengajuan.index', compact('applications'));
    }

    public function show(int $id): View
    {
        $application = InternshipApplication::with(['user', 'document', 'verifier'])->findOrFail($id);

        return view('admin.pengajuan.show', compact('application'));
    }

    /**
     * Jalankan verifikasi integritas SHA-256 pada CV yang tersimpan di Supabase Storage.
     */
    public function verify(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $application = InternshipApplication::with('document')->findOrFail($id);

        if (!$application->document) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Dokumen CV tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Dokumen CV tidak ditemukan.');
        }

        try {
            $result = $this->integrityService->verifyIntegrity($application->document);

            if ($request->expectsJson()) {
                return response()->json($result);
            }

            return back()->with('verification_result', $result);
        } catch (Exception $e) {
            Log::error('Verification failed: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Terjadi kesalahan saat memverifikasi integritas dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Memberikan keputusan pengajuan magang (Diterima / Ditolak) beserta catatan admin.
     */
    public function decide(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:' . InternshipApplication::STATUS_DITERIMA . ',' . InternshipApplication::STATUS_DITOLAK],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'status.required' => 'Keputusan pengajuan wajib dipilih.',
            'status.in' => 'Keputusan tidak valid.',
        ]);

        $application = InternshipApplication::findOrFail($id);

        $application->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $statusText = $request->status === InternshipApplication::STATUS_DITERIMA ? 'DITERIMA' : 'DITOLAK';

        return redirect()->route('admin.pengajuan.show', $application->id)
            ->with('success', "Keputusan berhasil disimpan: Pengajuan magang status {$statusText}.");
    }

    /**
     * Download / Stream CV mahasiswa dari Supabase Storage.
     */
    public function downloadCv(int $id): Response
    {
        $application = InternshipApplication::with('document')->findOrFail($id);

        if (!$application->document) {
            abort(404, 'Dokumen CV tidak ditemukan.');
        }

        try {
            $fileContent = $this->storageService->download($application->document->storage_path);
        } catch (Exception $e) {
            abort(500, 'Gagal mengambil dokumen CV dari cloud: ' . $e->getMessage());
        }

        return response($fileContent, 200, [
            'Content-Type' => $application->document->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $application->document->original_filename . '"',
        ]);
    }

    /**
     * Simulasi modifikasi data untuk mendemonstrasikan kemampuan deteksi SHA-256.
     * Fitur pengujian penelitian akademis.
     */
    public function simulateTamper(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $application = InternshipApplication::with('document')->findOrFail($id);
        $document = $application->document;

        if (!$document) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $action = $request->input('action', 'tamper_cloud_file');

        try {
            if ($action === 'tamper_cloud_file') {
                // Unduh file asli dari Supabase, tambahkan byte acak (modifikasi), lalu unggah kembali
                $originalContent = $this->storageService->download($document->storage_path);
                
                // Simpan backup di session atau temp file jika belum ada
                session(['backup_content_' . $document->id => base64_encode($originalContent)]);

                // Modifikasi file (tambahkan data byte tersembunyi yang merusak checksum SHA-256)
                $tamperedContent = $originalContent . "\n%-- MODIFIED_BY_TAMPER_SIMULATION_FOR_RESEARCH_" . time() . " --%";

                $tmpFile = tempnam(sys_get_temp_dir(), 'tamper_');
                file_put_contents($tmpFile, $tamperedContent);

                $this->storageService->upload($document->storage_path, $tmpFile, $document->mime_type ?: 'application/pdf');
                unlink($tmpFile);

                $msg = 'Simulasi manipulasi BERHASIL: File CV pada cloud storage telah dimodifikasi secara artifisial. Silakan jalankan Verifikasi Integritas untuk membuktikan deteksi SHA-256!';
            } elseif ($action === 'restore_cloud_file') {
                // Pulihkan file asli jika backup ada di session
                $encodedBackup = session('backup_content_' . $document->id);
                if ($encodedBackup) {
                    $originalContent = base64_decode($encodedBackup);
                    $tmpFile = tempnam(sys_get_temp_dir(), 'restore_');
                    file_put_contents($tmpFile, $originalContent);

                    $this->storageService->upload($document->storage_path, $tmpFile, $document->mime_type ?: 'application/pdf');
                    unlink($tmpFile);
                    session()->forget('backup_content_' . $document->id);

                    $msg = 'File CV di cloud storage berhasil dipulihkan ke versi asli. Verifikasi ulang sekarang akan menghasilkan status VALID.';
                } else {
                    $msg = 'File backup sesi tidak ditemukan. File tetap pada kondisi saat ini.';
                }
            } elseif ($action === 'tamper_db_hash') {
                // Ubah 1 karakter terakhir pada hash referensi database
                $orig = $document->original_sha256;
                $lastChar = substr($orig, -1);
                $newChar = ($lastChar === '0') ? '1' : '0';
                $tamperedHash = substr($orig, 0, -1) . $newChar;

                session(['orig_hash_' . $document->id => $orig]);
                $document->update(['original_sha256' => $tamperedHash]);

                $msg = 'Simulasi hash mismatch berhasil diaktifkan pada metadata database.';
            } elseif ($action === 'restore_db_hash') {
                $orig = session('orig_hash_' . $document->id);
                if ($orig) {
                    $document->update(['original_sha256' => $orig]);
                    session()->forget('orig_hash_' . $document->id);
                    $msg = 'Hash referensi di database berhasil dipulihkan.';
                } else {
                    $msg = 'Tidak ada riwayat hash sebelumnya untuk dipulihkan.';
                }
            } else {
                $msg = 'Aksi simulasi tidak dikenal.';
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => $msg]);
            }

            return back()->with('info', $msg);
        } catch (Exception $e) {
            Log::error('Tamper simulation error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menjalankan simulasi: ' . $e->getMessage());
        }
    }
}
