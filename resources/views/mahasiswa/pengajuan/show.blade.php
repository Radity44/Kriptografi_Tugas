@extends('layouts.app')

@section('title', 'Detail Pengajuan Magang')

@section('content')
<div class="mb-3">
    <a href="{{ route('mahasiswa.pengajuan.index') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Pengajuan
    </a>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Detail Pengajuan -->
    <div class="col-lg-7">
        <div class="card card-custom p-4 mb-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3 pb-3 border-bottom">
                <div>
                    <span class="badge bg-secondary mb-1">ID Pengajuan #{{ $application->id }}</span>
                    <h4 class="fw-bold text-dark mb-0">{{ $application->company_name }}</h4>
                    <span class="text-primary fw-semibold">{{ $application->internship_position }}</span>
                </div>
                <div class="text-end">
                    <span class="badge badge-status {{ $application->status_badge_class }} fs-6">
                        {{ $application->status_label }}
                    </span>
                    <div class="text-muted small mt-1">Diajukan: {{ $application->created_at->translatedFormat('d M Y, H:i') }}</div>
                </div>
            </div>

            <!-- Keputusan / Catatan Admin Jika Ada -->
            @if($application->status !== \App\Models\InternshipApplication::STATUS_MENUNGGU)
                <div class="alert {{ $application->status === \App\Models\InternshipApplication::STATUS_DITERIMA ? 'alert-success' : 'alert-danger' }} mb-4" role="alert">
                    <h6 class="alert-heading fw-bold d-flex align-items-center gap-2 mb-2">
                        <i class="bi {{ $application->status === \App\Models\InternshipApplication::STATUS_DITERIMA ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                        Keputusan Admin: Pengajuan {{ strtoupper($application->status) }}
                    </h6>
                    <p class="mb-1 small">
                        <strong>Ditinjau oleh:</strong> {{ $application->verifier->name ?? 'Admin Magang' }} pada {{ $application->verified_at ? $application->verified_at->translatedFormat('d F Y, H:i') : '-' }}
                    </p>
                    @if($application->admin_notes)
                        <div class="mt-2 pt-2 border-top border-opacity-25 border-dark small">
                            <strong>Catatan Admin:</strong>
                            <div class="p-2 bg-white bg-opacity-75 rounded mt-1">{{ $application->admin_notes }}</div>
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-warning mb-4 d-flex align-items-center gap-2 small" role="alert">
                    <i class="bi bi-hourglass-split fs-4 flex-shrink-0"></i>
                    <div>
                        <strong>Pengajuan Sedang Dalam Peninjauan:</strong> Dokumen CV Anda telah diamankan pada cloud storage dan sedang menunggu pemeriksaan serta verifikasi integritas oleh Admin.
                    </div>
                </div>
            @endif

            <h6 class="fw-bold text-muted text-uppercase small mb-2">Deskripsi / Motivasi Mahasiswa</h6>
            <div class="p-3 bg-light rounded text-dark mb-0" style="white-space: pre-line;">{{ $application->application_description }}</div>
        </div>
    </div>

    <!-- Kolom Kanan: Metadata Dokumen & SHA-256 -->
    <div class="col-lg-5">
        <div class="card card-custom p-4 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-primary"></i> Integritas Dokumen CV
            </h5>

            @if($application->document)
                <div class="mb-3">
                    <label class="text-muted small fw-semibold d-block">Nama Dokumen Asli</label>
                    <span class="fw-semibold text-dark">{{ $application->document->original_filename }}</span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="text-muted small fw-semibold d-block">Ukuran File</label>
                        <span>{{ $application->document->formatted_file_size }}</span>
                    </div>
                    <div class="col-6">
                        <label class="text-muted small fw-semibold d-block">Tipe Dokumen</label>
                        <span>{{ $application->document->mime_type }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-semibold d-block">Penyimpanan Cloud</label>
                    <span class="badge bg-dark">
                        <i class="bi bi-cloud-check me-1"></i> Supabase Storage (Private)
                    </span>
                </div>

                <div class="mb-3">
                    <label class="text-muted small fw-semibold d-block mb-1">
                        Sidik Jari Asli (Original SHA-256 Hash)
                        <i class="bi bi-info-circle" title="Dihitung secara lokal sebelum file diunggah ke cloud"></i>
                    </label>
                    <div class="hash-code">{{ $application->document->original_sha256 }}</div>
                    <div class="form-text small text-muted mt-1">
                        Algoritma SHA-256 menghasilkan 64 karakter hex unik sebagai acuan verifikasi keaslian dokumen.
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <a href="{{ route('mahasiswa.pengajuan.cv', $application->id) }}" target="_blank" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Buka / Unduh Dokumen CV
                    </a>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-file-earmark-x fs-2 d-block mb-2"></i>
                    Tidak ada dokumen terlampir.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
