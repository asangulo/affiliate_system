<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = User::with('roles')
            ->when($request->filled('search'), fn ($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->filled('role'), fn ($q) =>
                $q->whereHas('roles', fn ($r) => $r->where('name', $request->role))
            )
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->status === 'active');
            })
            ->orderBy('name');

        return Inertia::render('Admin/Users/Index', [
            'users'   => $query->paginate(15)->withQueryString(),
            'roles'   => Role::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────
    public function create()
    {
        return Inertia::render('Admin/Users/Create', [
            'roles'      => Role::orderBy('name')->get(['id', 'name']),
            'affiliates' => Affiliate::active()
                ->doesntHave('user')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'id_number']),
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => ['required', Password::min(8)->mixedCase()->numbers()],
            'role'         => 'required|exists:roles,name',
            'affiliate_id' => 'nullable|exists:affiliates,id',
            'is_active'    => 'boolean',
        ]);

        $user = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'is_active'    => $validated['is_active'] ?? true,
            'affiliate_id' => $validated['affiliate_id'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('message', "Usuario {$user->name} creado correctamente.");
    }

    // ── EDIT ──────────────────────────────────────────────────
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user'       => $user->load('roles'),
            'roles'      => Role::orderBy('name')->get(['id', 'name']),
            'affiliates' => Affiliate::active()
                ->where(fn ($q) =>
                    $q->doesntHave('user')->orWhereHas('user', fn ($u) => $u->where('id', $user->id))
                )
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'id_number']),
        ]);
    }

    // ── UPDATE ────────────────────────────────────────────────
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => "required|email|unique:users,email,{$user->id}",
            'password'     => ['nullable', Password::min(8)->mixedCase()->numbers()],
            'role'         => 'required|exists:roles,name',
            'affiliate_id' => 'nullable|exists:affiliates,id',
        ]);

        $data = [
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'affiliate_id' => $validated['affiliate_id'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('message', "Usuario {$user->name} actualizado.");
    }

    // ── TOGGLE STATUS ─────────────────────────────────────────
    // Nunca se eliminan — se activan/inactivan
    public function toggleStatus(User $user)
    {
        // No se puede inactivar el propio super admin
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes inactivar tu propia cuenta.');
        }

        $user->toggleStatus();

        $msg = $user->is_active
            ? "Usuario {$user->name} activado."
            : "Usuario {$user->name} inactivado.";

        return back()->with('message', $msg);
    }
}