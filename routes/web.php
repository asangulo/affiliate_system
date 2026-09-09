<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
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

Route::post('/contacto', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

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
//  MANUAL DE USUARIO — cualquier usuario autenticado
// ══════════════════════════════════════════════
Route::get('/manual', function () {
    return Inertia::render('Manual/Index');
})->middleware(['auth', 'verified'])->name('manual');

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

    // Gestión de roles y permisos
    Route::get('/roles',                [RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles',               [RoleController::class, 'storeRole'])->name('roles.store');
    Route::patch('/roles/{role}',       [RoleController::class, 'updateRole'])->name('roles.update');
    Route::delete('/roles/{role}',      [RoleController::class, 'destroyRole'])->name('roles.destroy');
    Route::post('/permissions',         [RoleController::class, 'storePermission'])->name('permissions.store');
});

// ══════════════════════════════════════════════
//  PORTAL DEL CLIENTE — solo rol cliente
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified', 'role:cliente'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Portal/Index');
    })->name('index');
});

// ══════════════════════════════════════════════
//  SEDES, PAGOS, HISTORIAL, REPORTES
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->group(function () {

    // ── SEDES ────────────────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/sedes',                [BranchController::class, 'index'])->name('branches.index');
        Route::get('/sedes/crear',          [BranchController::class, 'create'])->name('branches.create');
        Route::post('/sedes',               [BranchController::class, 'store'])->name('branches.store');
        Route::get('/sedes/{branch}/editar',[BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/sedes/{branch}',       [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/sedes/{branch}',    [BranchController::class, 'destroy'])->name('branches.destroy');
        Route::patch('/sedes/{branch}/estado', [BranchController::class, 'toggle'])->name('branches.toggle');
    });

    // ── PAGOS (personal interno) ─────────────────────────────
    Route::middleware('role:super_admin|empleado')->group(function () {
        Route::get('/pagos',                    [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/pagos/cartera',            [PaymentController::class, 'portfolio'])->name('payments.portfolio');
        Route::get('/pagos/registrar',          [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/pagos',                   [PaymentController::class, 'store'])->name('payments.store');
        Route::post('/pagos/multiple',          [PaymentController::class, 'storeBulk'])->name('payments.storeBulk');
        Route::delete('/pagos/{payment}',       [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('/pagos/afiliado/{affiliate}', [PaymentController::class, 'showAffiliate'])->name('payments.affiliate');
    });

    // ── MIS PAGOS (cliente) ──────────────────────────────────
    Route::get('/mis-pagos', [PaymentController::class, 'myPayments'])->name('payments.mine');

    // ── HISTORIAL ────────────────────────────────────────────
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/historial',           [ActivityLogController::class, 'index'])->name('activity.index');
        Route::delete('/historial/limpiar',[ActivityLogController::class, 'prune'])->name('activity.prune');
    });

    Route::get('/historial/{type}/{id}', [ActivityLogController::class, 'forSubject'])
         ->name('activity.subject');

    // ── REPORTES ─────────────────────────────────────────────
    Route::middleware('role:super_admin|empleado')->group(function () {
        Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
    });

});

require __DIR__.'/auth.php';