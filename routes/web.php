<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CirculationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LibraryVisitController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\StudentPortalController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/mahasiswa')->name('home');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', fn () => view('simpus', ['page' => 'admin-login']))->name('login');
    Route::post('/login', [AuthController::class, 'adminLogin'])->middleware('throttle:6,1')->name('login.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/presensi', [LibraryVisitController::class, 'kiosk'])->name('visits.kiosk');
        Route::get('/presensi/qr', [LibraryVisitController::class, 'tokens'])->middleware('throttle:60,1')->name('visits.tokens');
        Route::get('/sirkulasi', [CirculationController::class, 'index'])->name('circulation');
        Route::post('/sirkulasi', [CirculationController::class, 'store'])->name('loans.store');
        Route::post('/pinjaman/{loan}/kembali', [CirculationController::class, 'storeReturn'])->name('loans.return');
        Route::post('/pinjaman/{loan}/perpanjang', [CirculationController::class, 'renew'])->name('loans.renew');
        Route::get('/katalog', [BookController::class, 'index'])->name('books.index');
        Route::post('/katalog', [BookController::class, 'store'])->name('books.store');
        Route::post('/eksemplar', [BookController::class, 'storeCopy'])->name('copies.store');
        Route::get('/anggota', [MemberController::class, 'index'])->name('members.index');
        Route::post('/anggota', [MemberController::class, 'store'])->name('members.store');
        Route::post('/anggota/{user}/status', [MemberController::class, 'toggleStatus'])->name('members.status');
    });
});

Route::prefix('mahasiswa')->name('student.')->group(function (): void {
    Route::get('/login', fn () => view('simpus', ['page' => 'login']))->name('login');
    Route::post('/login', [AuthController::class, 'studentLogin'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/daftar', fn () => view('simpus', ['page' => 'register']))->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:student')->group(function (): void {
        Route::get('/', [StudentPortalController::class, 'index'])->name('portal');
        Route::get('/pinjaman-mandiri', [StudentPortalController::class, 'checkout'])->name('checkout');
        Route::post('/pinjaman-mandiri', [StudentPortalController::class, 'storeLoan'])->name('checkout.store');
        Route::post('/pinjaman/{loan}/kembali', [StudentPortalController::class, 'storeReturn'])->name('loans.return');
        Route::post('/presensi/scan', [LibraryVisitController::class, 'scan'])->middleware('throttle:30,1')->name('visits.scan');
    });
});
