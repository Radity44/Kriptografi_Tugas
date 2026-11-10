@extends('layouts.app')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom p-4 shadow-sm">
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-2">
                    <i class="bi bi-shield-lock-fill fs-2"></i>
                </div>
                <h4 class="fw-bold mb-1">Masuk ke Akun</h4>
                <p class="text-muted small">Sistem Pengajuan Magang Mahasiswa</p>
            </div>

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Masukkan kata sandi">
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label small" for="remember">Ingat Sesi Masuk Saya</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </button>
            </form>

            <hr class="my-4 text-muted">

            <div class="text-center small">
                Belum memiliki akun mahasiswa?
                <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Daftar sekarang</a>
            </div>

            <!-- Demo Credentials Guide -->
            <div class="mt-4 p-3 bg-light rounded border border-light-subtle">
                <div class="fw-bold small text-muted mb-2"><i class="bi bi-info-circle me-1"></i> Akun Uji Coba Default:</div>
                <div class="small text-muted mb-1">
                    <strong>Admin:</strong> <code>admin@magang.ac.id</code> / <code>Admin123!</code>
                </div>
                <div class="small text-muted">
                    <strong>Mahasiswa:</strong> <code>mahasiswa@magang.ac.id</code> / <code>Mahasiswa123!</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

