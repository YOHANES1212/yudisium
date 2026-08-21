<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

// Root → redirect ke admin dashboard jika sudah login, atau ke login jika belum
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// ── Form Pendaftaran Peserta (publik) ────────────────────────────
Route::get('/pendaftaran', [PesertaController::class, 'create']);
Route::post('/pendaftaran', [PesertaController::class, 'store'])->name('pendaftaran.store');

// ── Auth (guest only) ────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')
    ->middleware('auth');

// ── Admin Panel (khusus Panitia / Admin logged in) ────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'panitia'])->group(function () {
    Route::get('/',              [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/peserta',       [AdminController::class, 'peserta'])->name('peserta');
    Route::post('/peserta/hadir',[AdminController::class, 'tandaiHadir'])->name('peserta.hadir');
    Route::post('/peserta/pembayaran', [AdminController::class, 'updatePembayaran'])->name('peserta.pembayaran');
    Route::get('/absensi',       [AdminController::class, 'absensi'])->name('absensi');
    Route::post('/absensi/scan', [AdminController::class, 'scanQr'])->name('absensi.scan');
    Route::get('/logs',          [AdminController::class, 'logs'])->name('logs');
    Route::get('/export',        [AdminController::class, 'export'])->name('export');
    Route::post('/refresh',      [AdminController::class, 'refresh'])->name('refresh');
});
