<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
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
//  ACCESO POR PERMISOS
//  Cada ruta exige un permiso (middleware "can:Nombre del permiso").
//  Los permisos se asignan a cada rol en Administración → Roles y Permisos.
//  El rol super_admin tiene acceso a todo (ver AppServiceProvider).
// ══════════════════════════════════════════════

// ── PANEL PRINCIPAL ──
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'can:Ver panel principal'])->name('dashboard');

// ── MANUAL DE USUARIO — cualquier usuario autenticado ──
Route::get('/manual', function () {
    return Inertia::render('Manual/Index');
})->middleware(['auth', 'verified'])->name('manual');

// ══════════════════════════════════════════════
//  AFILIADOS
//  IMPORTANTE: /create SIEMPRE antes de /{affiliate}
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->prefix('affiliates')->name('affiliates.')->group(function () {
    Route::get('/', [AffiliateController::class, 'index'])->name('index')->middleware('can:Ver afiliados');
    Route::get('/create', [AffiliateController::class, 'create'])->name('create')->middleware('can:Crear afiliados');   // ← antes de /{affiliate}
    Route::post('/', [AffiliateController::class, 'store'])->name('store')->middleware('can:Crear afiliados');
    Route::post('/import', [AffiliateController::class, 'import'])->name('import')->middleware('can:Importar afiliados');
    Route::get('/{affiliate}', [AffiliateController::class, 'show'])->name('show')->middleware('can:Ver afiliados');
    Route::get('/{affiliate}/edit', [AffiliateController::class, 'edit'])->name('edit')->middleware('can:Editar afiliados');
    Route::patch('/{affiliate}', [AffiliateController::class, 'update'])->name('update')->middleware('can:Editar afiliados');
    Route::patch('/{affiliate}/toggle-status', [AffiliateController::class, 'toggleStatus'])->name('toggleStatus')->middleware('can:Activar o inactivar afiliados');
    Route::post('/{affiliate}/deceased', [AffiliateController::class, 'deceased'])->name('deceased')->middleware('can:Editar afiliados');
});

// ══════════════════════════════════════════════
//  ADMINISTRACIÓN — USUARIOS
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('can:Ver usuarios');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('can:Crear usuarios');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('can:Crear usuarios');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('can:Editar usuarios');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('can:Editar usuarios');
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle')->middleware('can:Activar o inactivar usuarios');

    // Roles y permisos: se puede VER con permiso, pero solo el super_admin los MODIFICA
    // (si no, quien tenga este permiso podría darse a sí mismo cualquier otro).
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('can:Ver roles y permisos');
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/roles', [RoleController::class, 'storeRole'])->name('roles.store');
        Route::patch('/roles/{role}', [RoleController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroyRole'])->name('roles.destroy');
        Route::post('/permissions', [RoleController::class, 'storePermission'])->name('permissions.store');
    });
});

// ══════════════════════════════════════════════
//  PORTAL DEL CLIENTE
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified', 'can:Ver portal del cliente'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Portal/Index');
    })->name('index');
});

// ══════════════════════════════════════════════
//  SEDES, PLANES, PAGOS, HISTORIAL, REPORTES
// ══════════════════════════════════════════════
Route::middleware(['auth', 'verified'])->group(function () {

    // ── SEDES ────────────────────────────────────────────────
    Route::get('/sedes', [BranchController::class, 'index'])->name('branches.index')->middleware('can:Ver sedes');
    Route::get('/sedes/crear', [BranchController::class, 'create'])->name('branches.create')->middleware('can:Crear sedes');
    Route::post('/sedes', [BranchController::class, 'store'])->name('branches.store')->middleware('can:Crear sedes');
    Route::get('/sedes/{branch}/editar', [BranchController::class, 'edit'])->name('branches.edit')->middleware('can:Editar sedes');
    Route::put('/sedes/{branch}', [BranchController::class, 'update'])->name('branches.update')->middleware('can:Editar sedes');
    Route::delete('/sedes/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy')->middleware('can:Eliminar sedes');
    Route::patch('/sedes/{branch}/estado', [BranchController::class, 'toggle'])->name('branches.toggle')->middleware('can:Editar sedes');

    // ── PLANES ───────────────────────────────────────────────
    Route::get('/planes', [PlanController::class, 'index'])->name('plans.index')->middleware('can:Ver planes');
    Route::post('/planes', [PlanController::class, 'store'])->name('plans.store')->middleware('can:Crear planes');
    Route::put('/planes/{plan}', [PlanController::class, 'update'])->name('plans.update')->middleware('can:Editar planes');
    Route::patch('/planes/{plan}/estado', [PlanController::class, 'toggle'])->name('plans.toggle')->middleware('can:Editar planes');
    Route::delete('/planes/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy')->middleware('can:Eliminar planes');

    // ── PAGOS (personal interno) ─────────────────────────────
    Route::get('/pagos', [PaymentController::class, 'index'])->name('payments.index')->middleware('can:Ver pagos');
    Route::get('/pagos/cartera', [PaymentController::class, 'portfolio'])->name('payments.portfolio')->middleware('can:Ver pagos');
    Route::get('/pagos/registrar', [PaymentController::class, 'create'])->name('payments.create')->middleware('can:Registrar pagos');
    Route::post('/pagos', [PaymentController::class, 'store'])->name('payments.store')->middleware('can:Registrar pagos');
    Route::post('/pagos/multiple', [PaymentController::class, 'storeBulk'])->name('payments.storeBulk')->middleware('can:Registrar pagos');
    Route::delete('/pagos/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy')->middleware('can:Anular pagos');
    Route::get('/pagos/afiliado/{affiliate}', [PaymentController::class, 'showAffiliate'])->name('payments.affiliate')->middleware('can:Ver pagos');

    // ── MIS PAGOS (cliente) ──────────────────────────────────
    Route::get('/mis-pagos', [PaymentController::class, 'myPayments'])->name('payments.mine')->middleware('can:Ver mis pagos');

    // ── HISTORIAL ────────────────────────────────────────────
    Route::get('/historial', [ActivityLogController::class, 'index'])->name('activity.index')->middleware('can:Ver historial');
    Route::delete('/historial/limpiar', [ActivityLogController::class, 'prune'])->name('activity.prune')->middleware('role:super_admin');
    Route::get('/historial/{type}/{id}', [ActivityLogController::class, 'forSubject'])->name('activity.subject')->middleware('can:Ver historial');

    // ── REPORTES ─────────────────────────────────────────────
    Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index')->middleware('can:Ver reportes');

});

require __DIR__.'/auth.php';
