@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Panel Administrator</h3>
        <p class="text-muted mb-0">Kelola permohonan magang, verifikasi integritas file SHA-256, dan tentukan keputusan pengajuan.</p>
    </div>
    <a href="{{ route('admin.pengajuan.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-card-checklist"></i> Buka Seluruh Pengajuan
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
                    <div class="text-muted small fw-semibold">Pengajuan Diterima</div>
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
                    <div class="text-muted small fw-semibold">Pengajuan Ditolak</div>
                    <div class="fs-3 fw-bold text-dark">{{ $stats['ditolak'] }}</div>
                </div>
                <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-x-circle fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informasi Riset Kriptografi -->
<div class="card card-custom mb-4 bg-light border-primary border-opacity-25">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div class="p-3 rounded-circle bg-primary text-white">
                <i class="bi bi-shield-check fs-2"></i>
            </div>
            <div>
                <h5 class="fw-bold mb-1">Pusat Verifikasi Integritas SHA-256</h5>
                <p class="text-muted small mb-0">
                    Sistem mendeteksi setiap modifikasi atau manipulasi byte file pada penyimpanan cloud Supabase dengan membandingkan nilai SHA-256 saat verifikasi langsung terhadap hash referensi terenkripsi di PostgreSQL.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Pengajuan Perlu Verifikasi Terkini -->
<div class="card card-custom">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">Pengajuan Mahasiswa Masuk</h5>
        <a href="{{ route('admin.pengajuan.index') }}" class="small text-decoration-none">Kelola Seluruh Pengajuan &rarr;</a>
    </div>
    <div class="card-body p-0">
        @if($recentApplications->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted fs-1 d-block mb-2"></i>
                <h6 class="text-muted">Belum ada pengajuan magang yang masuk</h6>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Nama Mahasiswa</th>
                            <th>Perusahaan Tujuan</th>
                            <th>Posisi</th>
                            <th>Tanggal Masuk</th>
                            <th>Status Pengajuan</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentApplications as $item)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark">{{ $item->user->name }}</div>
                                    <div class="text-muted small">{{ $item->user->email }}</div>
                                </td>
                                <td class="fw-semibold">{{ $item->company_name }}</td>
                                <td>{{ $item->internship_position }}</td>
                                <td class="text-muted small">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td>
                                    <span class="badge badge-status {{ $item->status_badge_class }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.pengajuan.show', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-search me-1"></i> Periksa & Verifikasi
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

