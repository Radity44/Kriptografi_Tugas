@extends('layouts.app')

@section('title', 'Pemeriksaan Pengajuan & Verifikasi SHA-256')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.pengajuan.index') }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengajuan
    </a>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Info Mahasiswa, Pengajuan & Keputusan -->
    <div class="col-lg-6">
        <!-- Kartu Mahasiswa & Pengajuan -->
        <div class="card card-custom p-4 mb-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                <div>
                    <span class="badge bg-secondary mb-1">ID Pengajuan #{{ $application->id }}</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ $application->company_name }}</h4>
                    <span class="text-primary fw-semibold">{{ $application->internship_position }}</span>
                </div>
                <div class="text-end">
                    <span class="badge badge-status {{ $application->status_badge_class }} fs-6">
                        {{ $application->status_label }}
                    </span>
                    <div class="text-muted small mt-1">{{ $application->created_at->translatedFormat('d F Y, H:i') }}</div>
                </div>
            </div>

            <!-- Identitas Mahasiswa -->
            <div class="p-3 bg-light rounded mb-3 border">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-person-circle me-1 text-primary"></i> Data Pemohon</div>
                <div class="small text-muted"><strong>Nama:</strong> {{ $application->user->name }}</div>
                <div class="small text-muted"><strong>Email:</strong> {{ $application->user->email }}</div>
            </div>

            <h6 class="fw-bold text-muted text-uppercase small mb-2">Deskripsi / Motivasi Mahasiswa</h6>
            <div class="p-3 bg-light rounded text-dark mb-3" style="white-space: pre-line;">{{ $application->application_description }}</div>

            @if($application->verified_by)
                <div class="p-3 rounded bg-light border small text-muted">
                    <i class="bi bi-person-check me-1"></i> Terakhir diproses oleh: <strong>{{ $application->verifier->name ?? 'Admin' }}</strong> pada {{ $application->verified_at ? $application->verified_at->translatedFormat('d M Y, H:i') : '-' }}
                    @if($application->admin_notes)
                        <div class="mt-2 pt-2 border-top">
                            <strong>Catatan Admin Sebelumnya:</strong> {{ $application->admin_notes }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Kartu Keputusan Admin -->
        <div class="card card-custom p-4 shadow-sm">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-primary"></i> Keputusan Pengajuan Magang
            </h5>
            <p class="text-muted small mb-3">
                Tentukan keputusan permohonan magang mahasiswa ini setelah memeriksa dokumen dan integritas file.
            </p>

            <form method="POST" action="{{ route('admin.pengajuan.decide', $application->id) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Keputusan <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check p-3 border rounded flex-fill {{ $application->status === \App\Models\InternshipApplication::STATUS_DITERIMA ? 'border-success bg-success bg-opacity-10' : '' }}">
                            <input class="form-check-input" type="radio" name="status" id="statusDiterima" value="{{ \App\Models\InternshipApplication::STATUS_DITERIMA }}" {{ $application->status === \App\Models\InternshipApplication::STATUS_DITERIMA ? 'checked' : '' }} required>
                            <label class="form-check-label fw-semibold text-success d-block" for="statusDiterima">
                                <i class="bi bi-check-circle-fill me-1"></i> Diterima
                            </label>
                            <span class="small text-muted">Pengajuan magang disetujui.</span>
                        </div>
                        <div class="form-check p-3 border rounded flex-fill {{ $application->status === \App\Models\InternshipApplication::STATUS_DITOLAK ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                            <input class="form-check-input" type="radio" name="status" id="statusDitolak" value="{{ \App\Models\InternshipApplication::STATUS_DITOLAK }}" {{ $application->status === \App\Models\InternshipApplication::STATUS_DITOLAK ? 'checked' : '' }} required>
                            <label class="form-check-label fw-semibold text-danger d-block" for="statusDitolak">
                                <i class="bi bi-x-circle-fill me-1"></i> Ditolak
                            </label>
                            <span class="small text-muted">Pengajuan magang ditolak.</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="admin_notes" class="form-label fw-semibold">Catatan Keputusan Admin (Opsional)</label>
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3" placeholder="Berikan catatan atau alasan pendukung untuk mahasiswa...">{{ old('admin_notes', $application->admin_notes) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-save me-1"></i> Simpan Keputusan
                </button>
            </form>
        </div>
    </div>

    <!-- Kolom Kanan: Verifikasi SHA-256 & Simulasi Kriptografi -->
    <div class="col-lg-6">
        <!-- Kartu Metadata Dokumen -->
        <div class="card card-custom p-4 mb-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf text-danger"></i> Dokumen CV Mahasiswa
                </h5>
                @if($application->document)
                    <a href="{{ route('admin.pengajuan.cv', $application->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download me-1"></i> Unduh / Buka File
                    </a>
                @endif
            </div>

            @if($application->document)
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-borderless small mb-0">
                        <tr>
                            <td class="text-muted" style="width: 35%;">Nama File Asli</td>
                            <td class="fw-semibold text-dark">: {{ $application->document->original_filename }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ukuran File</td>
                            <td>: {{ $application->document->formatted_file_size }} ({{ number_format($application->document->file_size) }} bytes)</td>
                        </tr>
                        <tr>
                            <td class="text-muted">MIME Type</td>
                            <td>: <code>{{ $application->document->mime_type }}</code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Penyimpanan</td>
                            <td>: <span class="badge bg-dark"><i class="bi bi-cloud-check me-1"></i> Supabase Storage Private ({{ config('services.supabase.bucket', 'cv-mahasiswa') }})</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Path Dokumen</td>
                            <td>: <code class="small">{{ $application->document->storage_path }}</code></td>
                        </tr>
                    </table>
                </div>

                <div class="mb-2">
                    <label class="text-muted small fw-semibold d-block mb-1">
                        <i class="bi bi-key-fill text-primary me-1"></i> Hash Referensi Asli (Database PostgreSQL):
                    </label>
                    <div class="hash-code text-primary fw-bold" id="origHashDisplay">{{ $application->document->original_sha256 }}</div>
                    <div class="text-muted small mt-1">Hash dihitung sebelum file diunggah ke cloud storage.</div>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-file-earmark-x fs-2 d-block mb-2"></i>
                    Dokumen tidak ditemukan pada pengajuan ini.
                </div>
            @endif
        </div>

        @if($application->document)
            <!-- Kartu Interaktif Verifikasi Integritas SHA-256 -->
            <div class="card card-custom p-4 mb-4 shadow-sm border-2 border-primary">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-primary d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check"></i> Uji Integritas Dokumen (SHA-256)
                    </h5>
                </div>
                <p class="text-muted small mb-3">
                    Sistem akan mengambil file langsung dari Supabase Storage secara real-time, menghitung ulang SHA-256 dari byte data saat ini, dan membandingkannya dengan hash referensi asli.
                </p>

                <!-- Tombol Eksekusi Verifikasi -->
                <button type="button" class="btn btn-primary w-100 py-2 fw-semibold mb-3 d-flex align-items-center justify-content-center gap-2" id="btnVerify" onclick="runSha256Verification()">
                    <i class="bi bi-arrow-repeat" id="verifySpinner"></i> Jalankan Verifikasi Integritas SHA-256
                </button>

                <!-- Box Hasil Verifikasi -->
                <div id="verificationResultBox" class="{{ session('verification_result') ? '' : 'd-none' }}">
                    @php $res = session('verification_result'); @endphp
                    <div id="resultAlert" class="alert {{ ($res['is_valid'] ?? false) ? 'alert-success border-success' : 'alert-danger border-danger' }} p-3 mb-3 shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold fs-5 d-flex align-items-center gap-2" id="statusBadgeText">
                                @if($res['is_valid'] ?? false)
                                    <i class="bi bi-check-circle-fill text-success fs-4"></i> STATUS: VALID
                                @else
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-4"></i> STATUS: TAMPERED
                                @endif
                            </span>
                            <span class="badge bg-dark small" id="verifiedAtText">
                                {{ $res['verified_at'] ?? now()->translatedFormat('d M Y H:i:s') }}
                            </span>
                        </div>
                        <p class="small mb-0" id="statusNotes">
                            {{ $res['notes'] ?? '' }}
                        </p>
                    </div>

                    <!-- Perbandingan Hash Berdampingan -->
                    <div class="p-3 bg-light rounded border">
                        <div class="fw-bold small text-muted text-uppercase mb-2">Hasil Perbandingan Hash Kriptografi:</div>
                        
                        <div class="mb-2">
                            <div class="small fw-semibold text-muted">1. Hash Asli Saat Diunggah (PostgreSQL):</div>
                            <div class="hash-code text-secondary" id="displayOriginalHash">{{ $res['original_hash'] ?? $application->document->original_sha256 }}</div>
                        </div>

                        <div class="mb-2">
                            <div class="small fw-semibold text-muted">2. Hash File Saat Ini (Diambil dari Cloud Storage):</div>
                            <div class="hash-code {{ ($res['is_valid'] ?? false) ? 'text-success fw-bold' : 'text-danger fw-bold' }}" id="displayCurrentHash">{{ $res['current_hash'] ?? '-' }}</div>
                        </div>

                        <div class="mt-2 pt-2 border-top small" id="matchConclusion">
                            @if(isset($res))
                                @if($res['is_valid'])
                                    <span class="text-success fw-semibold"><i class="bi bi-check2-all me-1"></i> Seluruh 64 karakter heksadesimal identik (hash_equals = TRUE). Integritas dokumen valid.</span>
                                @else
                                    <span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Ditemukan ketidakcocokan karakter hash (hash_equals = FALSE). Dokumen telah termanipulasi.</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Demonstrasi Riset: Simulasi Manipulasi Data -->
            <div class="card card-custom p-4 shadow-sm bg-warning bg-opacity-10 border-warning">
                <div class="d-flex align-items-center gap-2 mb-2 text-warning-emphasis">
                    <i class="bi bi-mortarboard-fill fs-5"></i>
                    <h6 class="fw-bold mb-0">Panel Demonstrasi Penelitian (Uji Deteksi SHA-256)</h6>
                </div>
                <p class="small text-muted mb-3">
                    Gunakan tombol simulasi di bawah ini untuk menguji kemampuan algoritma SHA-256 dalam mendeteksi manipulasi dokumen pada cloud storage:
                </p>

                <div class="d-flex flex-column gap-2">
                    <div class="d-flex gap-2">
                        <!-- Simulasi Modifikasi File Cloud -->
                        <form method="POST" action="{{ route('admin.pengajuan.tamper', $application->id) }}" class="flex-fill" onsubmit="return confirm('Simulasi akan memodifikasi byte data file CV pada cloud storage. Lanjutkan?')">
                            @csrf
                            <input type="hidden" name="action" value="tamper_cloud_file">
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100 text-start py-2">
                                <i class="bi bi-pencil-square me-1"></i> 1. Manipulasi File Cloud (Tamper)
                            </button>
                        </form>

                        <!-- Pulihkan File Cloud -->
                        <form method="POST" action="{{ route('admin.pengajuan.tamper', $application->id) }}" class="flex-fill">
                            @csrf
                            <input type="hidden" name="action" value="restore_cloud_file">
                            <button type="submit" class="btn btn-sm btn-outline-success w-100 text-start py-2">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> 2. Pulihkan File Cloud Asli
                            </button>
                        </form>
                    </div>

                    <div class="d-flex gap-2">
                        <!-- Simulasi Modifikasi Hash DB -->
                        <form method="POST" action="{{ route('admin.pengajuan.tamper', $application->id) }}" class="flex-fill">
                            @csrf
                            <input type="hidden" name="action" value="tamper_db_hash">
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100 text-start">
                                <i class="bi bi-database-exclamation me-1"></i> Modifikasi Hash DB
                            </button>
                        </form>

                        <!-- Pulihkan Hash DB -->
                        <form method="POST" action="{{ route('admin.pengajuan.tamper', $application->id) }}" class="flex-fill">
                            @csrf
                            <input type="hidden" name="action" value="restore_db_hash">
                            <button type="submit" class="btn btn-sm btn-outline-secondary w-100 text-start">
                                <i class="bi bi-database-check me-1"></i> Pulihkan Hash DB
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function runSha256Verification() {
        const btn = document.getElementById('btnVerify');
        const spinner = document.getElementById('verifySpinner');
        const resultBox = document.getElementById('verificationResultBox');
        const resultAlert = document.getElementById('resultAlert');
        const statusBadgeText = document.getElementById('statusBadgeText');
        const verifiedAtText = document.getElementById('verifiedAtText');
        const statusNotes = document.getElementById('statusNotes');
        const displayOrig = document.getElementById('displayOriginalHash');
        const displayCurr = document.getElementById('displayCurrentHash');
        const conclusion = document.getElementById('matchConclusion');

        btn.disabled = true;
        spinner.classList.add('spinner-border', 'spinner-border-sm');
        spinner.classList.remove('bi-arrow-repeat');

        try {
            const response = await fetch("{{ route('admin.pengajuan.verify', $application->id) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    "Accept": "application/json",
                    "Content-Type": "application/json"
                }
            });

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                throw new Error(errData.error || ('Gagal memverifikasi: HTTP ' + response.status));
            }

            const data = await response.json();

            // Tampilkan data ke UI
            resultBox.classList.remove('d-none');
            verifiedAtText.textContent = data.verified_at;
            statusNotes.textContent = data.notes;
            displayOrig.textContent = data.original_hash;
            displayCurr.textContent = data.current_hash;

            if (data.is_valid) {
                resultAlert.className = 'alert alert-success border-success p-3 mb-3 shadow-sm';
                statusBadgeText.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-4"></i> STATUS: VALID';
                displayCurr.className = 'hash-code text-success fw-bold';
                conclusion.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check2-all me-1"></i> Seluruh 64 karakter heksadesimal identik (hash_equals = TRUE). Integritas dokumen valid dan terbukti murni.</span>';
            } else {
                resultAlert.className = 'alert alert-danger border-danger p-3 mb-3 shadow-sm';
                statusBadgeText.innerHTML = '<i class="bi bi-exclamation-octagon-fill text-danger fs-4"></i> STATUS: TAMPERED (TERMODIFIKASI)';
                displayCurr.className = 'hash-code text-danger fw-bold';
                conclusion.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Terdeteksi perbedaan nilai hash (hash_equals = FALSE). Dokumen telah mengalami manipulasi/modifikasi data!</span>';
            }

            resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        } catch (error) {
            alert('Kesalahan saat memverifikasi: ' + error.message);
        } finally {
            btn.disabled = false;
            spinner.classList.remove('spinner-border', 'spinner-border-sm');
            spinner.classList.add('bi-arrow-repeat');
        }
    }
</script>
@endpush
