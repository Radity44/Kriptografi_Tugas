@extends('layouts.app')

@section('title', 'Daftar Pengajuan Magang Mahasiswa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Daftar Seluruh Pengajuan Magang</h3>
        <p class="text-muted mb-0">Kelola dan periksa seluruh berkas permohonan magang mahasiswa.</p>
    </div>
</div>

<!-- Filter dan Pencarian -->
<div class="card card-custom p-3 mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-md-7">
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.pengajuan.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Semua Pengajuan
                </a>
                <a href="{{ route('admin.pengajuan.index', ['status' => \App\Models\InternshipApplication::STATUS_MENUNGGU, 'search' => request('search')]) }}" class="btn btn-sm {{ request('status') === \App\Models\InternshipApplication::STATUS_MENUNGGU ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' }}">
                    <i class="bi bi-hourglass-split me-1"></i> Menunggu
                </a>
                <a href="{{ route('admin.pengajuan.index', ['status' => \App\Models\InternshipApplication::STATUS_DITERIMA, 'search' => request('search')]) }}" class="btn btn-sm {{ request('status') === \App\Models\InternshipApplication::STATUS_DITERIMA ? 'btn-success fw-bold' : 'btn-outline-success' }}">
                    <i class="bi bi-check-circle me-1"></i> Diterima
                </a>
                <a href="{{ route('admin.pengajuan.index', ['status' => \App\Models\InternshipApplication::STATUS_DITOLAK, 'search' => request('search')]) }}" class="btn btn-sm {{ request('status') === \App\Models\InternshipApplication::STATUS_DITOLAK ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
                    <i class="bi bi-x-circle me-1"></i> Ditolak
                </a>
            </div>
        </div>
        <div class="col-md-5">
            <form method="GET" action="{{ route('admin.pengajuan.index') }}" class="d-flex gap-2">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama mahasiswa / perusahaan...">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
                    @if(request('search'))
                        <a href="{{ route('admin.pengajuan.index', ['status' => request('status')]) }}" class="btn btn-outline-secondary" title="Reset filter pencarian">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabel Pengajuan -->
<div class="card card-custom">
    <div class="card-body p-0">
        @if($applications->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-search text-muted fs-1 d-block mb-2"></i>
                <h6 class="text-muted">Tidak ditemukan data pengajuan</h6>
                <p class="text-muted small">Coba ubah kata kunci pencarian atau filter status.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Mahasiswa</th>
                            <th>Perusahaan Tujuan</th>
                            <th>Posisi</th>
                            <th>Berkas CV</th>
                            <th>Status Pengajuan</th>
                            <th>Tanggal Masuk</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $index => $item)
                            <tr>
                                <td class="ps-3 text-muted">{{ $applications->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $item->user->name }}</div>
                                    <div class="text-muted small">{{ $item->user->email }}</div>
                                </td>
                                <td class="fw-semibold">{{ $item->company_name }}</td>
                                <td>{{ $item->internship_position }}</td>
                                <td>
                                    @if($item->document)
                                        <a href="{{ route('admin.pengajuan.cv', $item->id) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                                            {{ Str::limit($item->document->original_filename, 18) }}
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-status {{ $item->status_badge_class }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.pengajuan.show', $item->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-shield-check me-1"></i> Periksa & Verifikasi
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="p-3 border-top">
                    {{ $applications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

