@extends('layouts.app')

@section('title', 'Riwayat Pengajuan Magang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Riwayat Pengajuan Magang</h3>
        <p class="text-muted mb-0">Daftar seluruh pengajuan magang yang telah Anda kirimkan.</p>
    </div>
    <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-plus-circle"></i> Ajukan Magang Baru
    </a>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        @if($applications->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-file-earmark-x text-muted fs-1 d-block mb-2"></i>
                <h6 class="text-muted">Tidak ada data riwayat pengajuan</h6>
                <p class="text-muted small mb-3">Anda belum mengirimkan pengajuan magang apapun.</p>
                <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-primary btn-sm">Ajukan Sekarang</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Nama Perusahaan</th>
                            <th>Posisi Magang</th>
                            <th>Tanggal Pengajuan</th>
                            <th>File CV</th>
                            <th>Status Pengajuan</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $index => $item)
                            <tr>
                                <td class="ps-3 text-muted">{{ $applications->firstItem() + $index }}</td>
                                <td class="fw-semibold text-dark">{{ $item->company_name }}</td>
                                <td>{{ $item->internship_position }}</td>
                                <td class="text-muted small">{{ $item->created_at->translatedFormat('d F Y, H:i') }}</td>
                                <td>
                                    @if($item->document)
                                        <a href="{{ route('mahasiswa.pengajuan.cv', $item->id) }}" target="_blank" class="badge bg-light text-primary border text-decoration-none p-2">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                                            {{ Str::limit($item->document->original_filename, 22) }}
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
                                <td class="text-end pe-3">
                                    <a href="{{ route('mahasiswa.pengajuan.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-info-circle me-1"></i> Detail Status
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
