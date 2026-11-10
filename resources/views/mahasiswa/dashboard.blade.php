@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Dashboard Mahasiswa</h3>
        <p class="text-muted mb-0">Selamat datang, {{ auth()->user()->name }}. Pantau status permohonan magang Anda di sini.</p>
    </div>
    <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-circle"></i> Ajukan Magang Baru
    </a>
</div>

<!-- Statistik Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-custom stat-card p-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">Total Pengajuan</div>
                    <div class="fs-3 fw-bold text-dark">{{ $stats['total'] }}</div>
                </div>
                <div class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-folder2-open fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-custom stat-card p-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">Menunggu Verifikasi</div>
                    <div class="fs-3 fw-bold text-dark">{{ $stats['menunggu'] }}</div>
                </div>
                <div class="p-2 rounded bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-hourglass-split fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-custom stat-card p-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">Diterima</div>
                    <div class="fs-3 fw-bold text-dark">{{ $stats['diterima'] }}</div>
                </div>
                <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card card-custom stat-card p-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold">Ditolak</div>
                    <div class="fs-3 fw-bold text-dark">{{ $stats['ditolak'] }}</div>
                </div>
                <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pengajuan Terkini -->
<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">Pengajuan Terkini</h5>
        <a href="{{ route('mahasiswa.pengajuan.index') }}" class="small text-decoration-none">Lihat Semua Riwayat &rarr;</a>
    </div>
    <div class="card-body p-0">
        @if($recentApplications->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1 d-block mb-2"></i>
                <h6 class="text-muted">Belum ada pengajuan magang</h6>
                <p class="text-muted small mb-3">Mulai ajukan permohonan magang Anda sekarang dengan mengunggah CV format PDF.</p>
                <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Buat Pengajuan Pertama
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Perusahaan / Instansi</th>
                            <th>Posisi</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Dokumen CV</th>
                            <th>Status Pengajuan</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentApplications as $item)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $item->company_name }}</td>
                                <td>{{ $item->internship_position }}</td>
                                <td class="text-muted small">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td>
                                    @if($item->document)
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                                            {{ Str::limit($item->document->original_filename, 20) }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $item->status_badge_class }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('mahasiswa.pengajuan.show', $item->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
