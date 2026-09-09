<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()
            ->withCount('affiliates')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('manager_name', 'like', "%{$search}%");
                });
            })
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Branches/Index', [
            'branches' => $branches,
            'filters'  => $request->only(['search', 'status']),
            'stats'    => [
                'total'    => Branch::count(),
                'active'   => Branch::where('is_active', true)->count(),
                'inactive' => Branch::where('is_active', false)->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Branches/Create');
    }

    public function store(Request $request)
    {
        $data = $this->validateBranch($request);

        Branch::create($data);

        return redirect()->route('branches.index')
                         ->with('message', 'Sede creada correctamente.');
    }

    public function edit(Branch $branch)
    {
        $branch->loadCount('affiliates');

        return Inertia::render('Branches/Edit', [
            'branch' => $branch,
        ]);
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $this->validateBranch($request, $branch->id);

        $branch->update($data);

        return redirect()->route('branches.index')
                         ->with('message', 'Sede actualizada correctamente.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->affiliates()->exists()) {
            return back()->with('error',
                'No se puede eliminar: la sede tiene afiliados asociados. Desactívala en su lugar.');
        }

        $branch->delete();

        return redirect()->route('branches.index')
                         ->with('message', 'Sede eliminada.');
    }

    /**
     * Activar / desactivar sin entrar a editar.
     */
    public function toggle(Branch $branch)
    {
        $branch->update(['is_active' => !$branch->is_active]);

        return back()->with('message',
            $branch->is_active ? 'Sede activada.' : 'Sede desactivada.');
    }

    private function validateBranch(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                Rule::unique('branches', 'name')->ignore($ignoreId),
            ],
            'code' => [
                'nullable', 'string', 'max:20',
                Rule::unique('branches', 'code')->ignore($ignoreId),
            ],
            'city'         => ['nullable', 'string', 'max:120'],
            'department'   => ['nullable', 'string', 'max:120'],
            'address'      => ['nullable', 'string', 'max:200'],
            'phone'        => ['nullable', 'string', 'max:40'],
            'email'        => ['nullable', 'email', 'max:150'],
            'manager_name' => ['nullable', 'string', 'max:150'],
            'opened_at'    => ['nullable', 'date'],
            'notes'        => ['nullable', 'string', 'max:2000'],
            'is_active'    => ['boolean'],
        ], [
            'name.required' => 'El nombre de la sede es obligatorio.',
            'name.unique'   => 'Ya existe una sede con ese nombre.',
            'code.unique'   => 'Ya existe una sede con ese código.',
            'email.email'   => 'El correo no tiene un formato válido.',
        ]);
    }
}
