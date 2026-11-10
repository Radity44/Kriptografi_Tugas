<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Models\InternshipApplication;
use App\Services\DocumentIntegrityService;
use App\Services\SupabaseStorageService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MahasiswaController extends Controller
{
    public function __construct(
        protected SupabaseStorageService $storageService,
        protected DocumentIntegrityService $integrityService
    ) {}

    public function dashboard(): View
    {
        $userId = Auth::id();

        $stats = [
            'total' => InternshipApplication::where('user_id', $userId)->count(),
            'menunggu' => InternshipApplication::where('user_id', $userId)->where('status', InternshipApplication::STATUS_MENUNGGU)->count(),
            'diterima' => InternshipApplication::where('user_id', $userId)->where('status', InternshipApplication::STATUS_DITERIMA)->count(),
            'ditolak' => InternshipApplication::where('user_id', $userId)->where('status', InternshipApplication::STATUS_DITOLAK)->count(),
        ];

        $recentApplications = InternshipApplication::with('document')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        return view('mahasiswa.dashboard', compact('stats', 'recentApplications'));
    }

    public function index(): View
    {
        $applications = InternshipApplication::with(['document', 'verifier'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('mahasiswa.pengajuan.index', compact('applications'));
    }

    public function create(): View
    {
        return view('mahasiswa.pengajuan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'internship_position' => ['required', 'string', 'max:255'],
            'application_description' => ['required', 'string'],
            'cv' => [
                'required',
                'file',
                'mimes:pdf',
                'max:5120', // Maksimal 5 MB (5120 KB)
            ],
        ], [
            'company_name.required' => 'Nama instansi/perusahaan tujuan magang wajib diisi.',
            'internship_position.required' => 'Posisi magang yang diajukan wajib diisi.',
            'application_description.required' => 'Deskripsi atau alasan pengajuan magang wajib diisi.',
            'cv.required' => 'Dokumen CV wajib diunggah.',
            'cv.mimes' => 'Dokumen CV harus berformat PDF (.pdf).',
            'cv.max' => 'Ukuran dokumen CV tidak boleh melebihi 5 MB.',
        ]);

        $file = $request->file('cv');
        $originalFilename = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType() ?: 'application/pdf';

        // 1. Hitung SHA-256 hash file asli sebelum diunggah
        try {
            $sha256Hash = $this->integrityService->calculateFileHash($file->getRealPath());
        } catch (Exception $e) {
            Log::error('SHA-256 calculation error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['cv' => 'Gagal menghitung SHA-256 file: ' . $e->getMessage()]);
        }

        // 2. Buat storage path unik di Supabase
        $safeFilename = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $uniqueStoragePath = 'cv/' . Auth::id() . '/' . time() . '_' . Str::random(8) . '_' . $safeFilename . '.' . $extension;

        // 3. Upload ke Supabase Storage (private bucket)
        try {
            $this->storageService->upload($uniqueStoragePath, $file->getRealPath(), $mimeType);
        } catch (Exception $e) {
            Log::error('Supabase upload error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['cv' => 'Gagal mengunggah CV ke Supabase Storage: ' . $e->getMessage()]);
        }

        // 4. Simpan ke database dalam transaction
        DB::beginTransaction();
        try {
            $application = InternshipApplication::create([
                'user_id' => Auth::id(),
                'company_name' => $request->input('company_name'),
                'internship_position' => $request->input('internship_position'),
                'application_description' => $request->input('application_description'),
                'status' => InternshipApplication::STATUS_MENUNGGU,
            ]);

            ApplicationDocument::create([
                'application_id' => $application->id,
                'original_filename' => $originalFilename,
                'storage_path' => $uniqueStoragePath,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'original_sha256' => $sha256Hash,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Database save error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['cv' => 'Gagal menyimpan data pengajuan: ' . $e->getMessage()]);
        }

        return redirect()->route('mahasiswa.pengajuan.show', $application->id)
            ->with('success', 'Pengajuan magang dan CV berhasil disimpan! Hash SHA-256 telah dihitung dan tercatat.');
    }

    public function show(int $id): View
    {
        $application = InternshipApplication::with(['document', 'verifier'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('mahasiswa.pengajuan.show', compact('application'));
    }

    public function downloadCv(int $id): Response
    {
        $application = InternshipApplication::with('document')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

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
}
