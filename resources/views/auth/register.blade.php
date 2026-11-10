@extends('layouts.app')

@section('title', 'Pendaftaran Mahasiswa')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom p-4 shadow-sm">
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10 text-primary mb-2">
                    <i class="bi bi-person-plus-fill fs-2"></i>
                </div>
                <h4 class="fw-bold mb-1">Daftar Akun Mahasiswa</h4>
                <p class="text-muted small">Buat akun untuk mengajukan magang dan mengunggah CV</p>
            </div>

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Raditya">
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com">
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Minimal 6 karakter">
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi">
                    </div>
                </div>

                <div class="mb-3 p-2 bg-light rounded small text-muted">
                    <i class="bi bi-info-circle me-1"></i> Peran akun secara otomatis ditetapkan sebagai <strong>Mahasiswa</strong>.
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Selesaikan Pendaftaran
                </button>
            </form>

            <hr class="my-4 text-muted">

            <div class="text-center small">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Masuk di sini</a>
            </div>
        </div>
    </div>
</div>
@endsection

