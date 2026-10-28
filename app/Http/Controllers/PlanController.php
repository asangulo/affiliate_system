<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::query()
            ->withCount('affiliates')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->get();

        return Inertia::render('Plans/Index', [
            'plans'   => $plans,
            'filters' => $request->only(['search', 'status']),
            'stats'   => [
                'total'    => Plan::count(),
                'active'   => Plan::where('is_active', true)->count(),
                'inactive' => Plan::where('is_active', false)->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePlan($request);
        $data['is_active'] = $data['is_active'] ?? true;

        $plan = Plan::create($data);

        return back()->with('message', "Plan {$plan->name} creado correctamente.");
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validatePlan($request, $plan->id));

        return back()->with('message', "Plan {$plan->name} actualizado correctamente.");
    }

    /**
     * Activar o desactivar sin entrar a editar.
     */
    public function toggle(Plan $plan)
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return back()->with('message', $plan->is_active
            ? "Plan {$plan->name} activado."
            : "Plan {$plan->name} desactivado: ya no se ofrece al crear afiliados.");
    }

    public function destroy(Plan $plan)
    {
        if ($plan->affiliates()->exists()) {
            return back()->with('error',
                'No se puede eliminar: el plan tiene afiliados asociados. Desactívalo en su lugar.');
        }

        $plan->delete();

        return back()->with('message', 'Plan eliminado.');
    }

    private function validatePlan(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                Rule::unique('plans', 'name')->ignore($ignoreId),
            ],
            'base_price'  => ['required', 'numeric', 'min:0', 'max:999999999'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active'   => ['sometimes', 'boolean'],
        ], [
            'name.required'       => 'El nombre del plan es obligatorio.',
            'name.unique'         => 'Ya existe un plan con ese nombre.',
            'base_price.required' => 'Indica el valor base de la cuota.',
        ]);
    }
}
