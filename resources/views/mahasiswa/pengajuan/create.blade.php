@extends('layouts.app')

@section('title', 'Ajukan Magang Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-3">
            <a href="{{ route('mahasiswa.dashboard') }}" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="card card-custom p-4 shadow-sm">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-file-earmark-arrow-up fs-3"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1">Formulir Pengajuan Magang</h4>
                    <p class="text-muted small mb-0">Lengkapi data tujuan magang dan unggah dokumen Curriculum Vitae (CV) Anda.</p>
                </div>
            </div>

            <!-- Notice Kriptografi -->
            <div class="alert alert-primary d-flex align-items-start gap-2 mb-4" role="alert">
                <i class="bi bi-shield-lock-fill fs-5 mt-1 flex-shrink-0"></i>
                <div class="small">
                    <strong>Pemberitahuan Integritas Kriptografi (SHA-256):</strong>
                    Saat Anda mengunggah CV, sistem kami akan langsung menghitung kode hash <strong>SHA-256</strong> dokumen Anda secara lokal sebelum dikirim ke penyimpanan cloud Supabase. Hash ini akan menjadi sidik jari digital permanen untuk memverifikasi keaslian dokumen Anda.
                </div>
            </div>

            <form method="POST" action="{{ route('mahasiswa.pengajuan.store') }}" enctype="multipart/form-data" id="formPengajuan">
                @csrf

                <div class="mb-3">
                    <label for="company_name" class="form-label fw-semibold">Nama Perusahaan / Instansi Tujuan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" required placeholder="Contoh: PT Telkom Indonesia (Persero) Tbk">
                    @error('company_name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="internship_position" class="form-label fw-semibold">Posisi Magang yang Diajukan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('internship_position') is-invalid @enderror" id="internship_position" name="internship_position" value="{{ old('internship_position') }}" required placeholder="Contoh: Cybersecurity Analyst / Software Engineer Intern">
                    @error('internship_position')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="application_description" class="form-label fw-semibold">Deskripsi / Motivasi Pengajuan <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('application_description') is-invalid @enderror" id="application_description" name="application_description" rows="4" required placeholder="Jelaskan alasan memilih perusahaan ini, kompetensi yang relevan, serta rencana periode magang...">{{ old('application_description') }}</textarea>
                    @error('application_description')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="cv" class="form-label fw-semibold">Dokumen CV (Curriculum Vitae) <span class="text-danger">*</span></label>
                    <input type="file" class="form-control @error('cv') is-invalid @enderror" id="cv" name="cv" accept="application/pdf" required>
                    <div class="form-text small text-muted">
                        <i class="bi bi-info-circle me-1"></i> Format wajib: <strong>PDF (.pdf)</strong>. Ukuran maksimum: <strong>5 MB</strong>. Dokumen akan disimpan pada Supabase Private Storage.
                    </div>
                    @error('cv')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    <!-- Preview Info File -->
                    <div id="fileInfoBox" class="mt-2 p-2 bg-light rounded small border d-none">
                        <span class="fw-semibold"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> File dipilih:</span>
                        <span id="fileName" class="text-dark"></span> (<span id="fileSize" class="text-muted"></span>)
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold" id="btnSubmit">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Simpan & Unggah CV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('cv').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const infoBox = document.getElementById('fileInfoBox');
        const nameSpan = document.getElementById('fileName');
        const sizeSpan = document.getElementById('fileSize');

        if (file) {
            // Validasi format PDF
            if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                alert('Peringatan: File yang dipilih bukan dokumen PDF! Silakan pilih file berekstensi .pdf.');
                e.target.value = '';
                infoBox.classList.add('d-none');
                return;
            }

            // Validasi ukuran maks 5MB = 5 * 1024 * 1024 bytes
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Peringatan: Ukuran file melebihi batas maksimum 5 MB. Ukuran file Anda: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB.');
                e.target.value = '';
                infoBox.classList.add('d-none');
                return;
            }

            // Tampilkan info
            nameSpan.textContent = file.name;
            sizeSpan.textContent = (file.size / 1024).toFixed(1) + ' KB';
            infoBox.classList.remove('d-none');
        } else {
            infoBox.classList.add('d-none');
        }
    });

    document.getElementById('formPengajuan').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menghitung SHA-256 & Mengunggah...';
    });
</script>
@endpush
