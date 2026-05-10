<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AffiliateController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/affiliates', [AffiliateController::class, 'index'])->name('affiliates.index');
    Route::get('/affiliates/{affiliate}', [AffiliateController::class, 'show'])->name('affiliates.show');
    Route::get('/affiliates/create', [AffiliateController::class, 'create'])->name('affiliates.create');
    Route::post('/affiliates', [AffiliateController::class, 'store'])->name('affiliates.store');
    Route::post('/affiliates/import', [AffiliateController::class, 'import'])->name('affiliates.import');
});

require __DIR__.'/auth.php';
