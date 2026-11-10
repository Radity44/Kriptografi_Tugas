<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Pengajuan Magang') — Integritas SHA-256</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --app-primary: #1e3a8a;
            --app-accent: #2563eb;
            --app-dark: #0f172a;
        }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-custom {
            background-color: #0f172a;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }
        .hash-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.85rem;
            word-break: break-all;
            background-color: #f1f5f9;
            padding: 0.35rem 0.6rem;
            border-radius: 0.375rem;
            border: 1px solid #e2e8f0;
            color: #0f172a;
        }
        .stat-card {
            border-radius: 0.75rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.1);
        }
        .badge-status {
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            border-radius: 2rem;
            font-weight: 600;
        }
        footer {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 1.25rem 0;
            font-size: 0.875rem;
            color: #64748b;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ url('/') }}">
                <span class="p-1 px-2 rounded bg-primary text-white fs-6">
                    <i class="bi bi-shield-check"></i> SHA-256
                </span>
                <span>Magang Mahasiswa</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMain">
                @auth
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                        @if(auth()->user()->isMahasiswa())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('mahasiswa.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.pengajuan.create') ? 'active fw-semibold' : '' }}" href="{{ route('mahasiswa.pengajuan.create') }}">
                                    <i class="bi bi-plus-circle me-1"></i> Ajukan Magang
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mahasiswa.pengajuan.index') ? 'active fw-semibold' : '' }}" href="{{ route('mahasiswa.pengajuan.index') }}">
                                    <i class="bi bi-journal-text me-1"></i> Riwayat Pengajuan
                                </a>
                            </li>
                        @elseif(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Dashboard Admin
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.pengajuan.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.pengajuan.index') }}">
                                    <i class="bi bi-card-checklist me-1"></i> Kelola Pengajuan Magang
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="d-flex align-items-center gap-3">
                        <div class="text-white text-end d-none d-md-block">
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <span class="badge {{ auth()->user()->isAdmin() ? 'bg-danger' : 'bg-info text-dark' }}">
                                <i class="bi {{ auth()->user()->isAdmin() ? 'bi-shield-lock' : 'bi-mortarboard' }} me-1"></i>
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                                <i class="bi bi-box-arrow-right me-1"></i> Keluar
                            </button>
                        </form>
                    </div>
                @else
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2" href="{{ route('register') }}">Daftar Mahasiswa</a>
                        </li>
                    </ul>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container py-4 flex-grow-1">
        <!-- Flash Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Terjadi kesalahan pengisian formulir:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-1 fw-semibold text-dark">Sistem Pengajuan Magang Mahasiswa</p>
            <p class="mb-0 text-muted small">
                Implementasi Penelitian Kriptografi: <strong>Integritas Data pada Penyimpanan Cloud Menggunakan SHA-256</strong>.
                Didukung oleh Laravel, Supabase PostgreSQL, & Supabase Storage.
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>

