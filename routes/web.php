<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MahasiswaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('mahasiswa.dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Mahasiswa routes
Route::middleware(['auth', 'mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan', [MahasiswaController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/create', [MahasiswaController::class, 'create'])->name('pengajuan.create');
    Route::post('/pengajuan', [MahasiswaController::class, 'store'])->name('pengajuan.store');
    Route::get('/pengajuan/{id}', [MahasiswaController::class, 'show'])->name('pengajuan.show');
    Route::get('/pengajuan/{id}/cv', [MahasiswaController::class, 'downloadCv'])->name('pengajuan.cv');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan', [AdminController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{id}', [AdminController::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{id}/verify', [AdminController::class, 'verify'])->name('pengajuan.verify');
    Route::post('/pengajuan/{id}/decide', [AdminController::class, 'decide'])->name('pengajuan.decide');
    Route::post('/pengajuan/{id}/tamper', [AdminController::class, 'simulateTamper'])->name('pengajuan.tamper');
    Route::get('/pengajuan/{id}/cv', [AdminController::class, 'downloadCv'])->name('pengajuan.cv');
});
