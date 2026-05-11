<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ══════════════════════════════════════════════
//  LANDING PAGE PÚBLICA
// ══════════════════════════════════════════════
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'    => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

// ══════════════════════════════════════════════
//  RUTAS AUTENTICADAS — PERFIL
// ══════════════════════════════════════════════
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ══════════════════════════════════════════════
//  DASHBOARD — acceso para super_admin y empleado
// ══════════════════════════════════════════════
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:super_admin|empleado'])->name('dashboard');

// ══════════════════════════════════════════════
//  AFILIADOS — super_admin y empleado
//  IMPORTANTE: /create SIEMPRE antes de /{affiliate}
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified', 'role:super_admin|empleado'])->prefix('affiliates')->name('affiliates.')->group(function () {
    Route::get('/',             [AffiliateController::class, 'index'])->name('index');
    Route::get('/create',       [AffiliateController::class, 'create'])->name('create');   // ← antes de /{affiliate}
    Route::post('/',            [AffiliateController::class, 'store'])->name('store');
    Route::post('/import',      [AffiliateController::class, 'import'])->name('import');
    Route::get('/{affiliate}',  [AffiliateController::class, 'show'])->name('show');
    Route::get('/{affiliate}/edit', [AffiliateController::class, 'edit'])->name('edit');
    Route::patch('/{affiliate}',    [AffiliateController::class, 'update'])->name('update');
    Route::patch('/{affiliate}/toggle-status', [AffiliateController::class, 'toggleStatus'])->name('toggleStatus');
});

// ══════════════════════════════════════════════
//  ADMINISTRACIÓN — solo super_admin
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {

    // Gestión de usuarios
    Route::get('/users',                    [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create',             [UserController::class, 'create'])->name('users.create');
    Route::post('/users',                   [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit',        [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}',           [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle',    [UserController::class, 'toggleStatus'])->name('users.toggle');

    // Gestión de roles
    Route::get('/roles',  [RoleController::class, 'index'])->name('roles.index');
});

// ══════════════════════════════════════════════
//  PORTAL DEL CLIENTE — solo rol cliente
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified', 'role:cliente'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Portal/Index');
    })->name('index');
});

require __DIR__.'/auth.php';