<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Branch;
use App\Models\Plan;
use App\Imports\AffiliatesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AffiliateController extends Controller
{
    // ── INDEX ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page', 10)
            : 10;

        $sortableColumns = ['full_name', 'id_number', 'contract_number', 'affiliation_date', 'created_at'];
        $sortBy        = in_array($request->input('sort_by', 'created_at'), $sortableColumns, true)
            ? $request->input('sort_by', 'created_at')
            : 'created_at';
        $sortDirection = $request->input('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $query = Affiliate::with(['branch', 'plan', 'beneficiaries']);

        // Filtro activo/inactivo
        $status = $request->input('status', 'active');
        if ($status === 'inactive') {
            $query->inactive();
        } elseif ($status === 'all') {
            // sin filtro
        } else {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name',       'like', "%{$search}%")
                  ->orWhere('id_number',     'like', "%{$search}%")
                  ->orWhere('contract_number','like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        $query->orderBy($sortBy, $sortDirection);

        return Inertia::render('Affiliates/Index', [
            'affiliates' => $query->paginate($perPage)->withQueryString(),
            'branches'   => Branch::orderBy('name')->get(['id', 'name']),
            'plans'      => Plan::orderBy('name')->get(['id', 'name']),
            'filters'    => $request->only([
                'search', 'sort_by', 'sort_direction',
                'per_page', 'branch_id', 'plan_id', 'status',
            ]),
        ]);
    }

    // ── CREATE ────────────────────────────────────────────────
    public function create()
    {
        return Inertia::render('Affiliates/Create', [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'plans'    => Plan::orderBy('name')->get(['id', 'name']),
        ]);
    }

    // ── STORE ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'id_number'        => 'required|string|unique:affiliates,id_number|max:20',
            'birth_date'       => 'nullable|date',
            'contract_number'  => 'nullable|string|max:50',
            'branch_id'        => 'required|exists:branches,id',
            'plan_id'          => 'required|exists:plans,id',
            'fee_amount'       => 'required|numeric|min:0',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:255',
            'neighborhood'     => 'nullable|string|max:100',
            'advisor_name'     => 'nullable|string|max:150',
            'affiliation_date' => 'nullable|date',
            // Beneficiarios (array opcional)
            'beneficiaries'              => 'nullable|array',
            'beneficiaries.*.full_name'  => 'required_with:beneficiaries|string|max:200',
            'beneficiaries.*.relationship' => 'nullable|string|max:50',
            'beneficiaries.*.age'        => 'nullable|string|max:10',
            'beneficiaries.*.auxilio'    => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $affiliate = Affiliate::create([
                'full_name'        => $validated['full_name'],
                'id_number'        => $validated['id_number'],
                'birth_date'       => $validated['birth_date'] ?? null,
                'contract_number'  => $validated['contract_number'] ?? null,
                'branch_id'        => $validated['branch_id'],
                'plan_id'          => $validated['plan_id'],
                'fee_amount'       => $validated['fee_amount'],
                'email'            => $validated['email'] ?? null,
                'phone'            => $validated['phone'] ?? null,
                'address'          => $validated['address'] ?? null,
                'neighborhood'     => $validated['neighborhood'] ?? null,
                'advisor_name'     => $validated['advisor_name'] ?? null,
                'affiliation_date' => $validated['affiliation_date'] ?? null,
                'is_active'        => true,
            ]);

            // Guardar beneficiarios si vienen
            if (!empty($validated['beneficiaries'])) {
                foreach ($validated['beneficiaries'] as $ben) {
                    $affiliate->beneficiaries()->create([
                        'full_name'    => $ben['full_name'],
                        'relationship' => $ben['relationship'] ?? null,
                        'age'          => $ben['age'] ?? null,
                        'auxilio'      => $ben['auxilio'] ?? null,
                        'is_active'    => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('affiliates.show', $affiliate)
                ->with('message', "Afiliado {$affiliate->full_name} registrado con éxito.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear afiliado: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Ocurrió un error al guardar. Inténtalo de nuevo.');
        }
    }

    // ── SHOW ──────────────────────────────────────────────────
    public function show(Affiliate $affiliate)
    {
        return Inertia::render('Affiliates/Show', [
            'affiliate' => $affiliate->load(['branch', 'plan', 'beneficiaries']),
        ]);
    }

    // ── EDIT ──────────────────────────────────────────────────
    public function edit(Affiliate $affiliate)
    {
        return Inertia::render('Affiliates/Edit', [
            'affiliate' => $affiliate->load('beneficiaries'),
            'branches'  => Branch::orderBy('name')->get(['id', 'name']),
            'plans'     => Plan::orderBy('name')->get(['id', 'name']),
        ]);
    }

    // ── UPDATE ────────────────────────────────────────────────
    public function update(Request $request, Affiliate $affiliate)
    {
        $validated = $request->validate([
            'full_name'        => 'required|string|max:255',
            'id_number'        => "required|string|max:20|unique:affiliates,id_number,{$affiliate->id}",
            'birth_date'       => 'nullable|date',
            'contract_number'  => 'nullable|string|max:50',
            'branch_id'        => 'required|exists:branches,id',
            'plan_id'          => 'required|exists:plans,id',
            'fee_amount'       => 'required|numeric|min:0',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:255',
            'neighborhood'     => 'nullable|string|max:100',
            'advisor_name'     => 'nullable|string|max:150',
            'affiliation_date' => 'nullable|date',
            'beneficiaries'              => 'nullable|array',
            'beneficiaries.*.id'         => 'nullable|exists:beneficiaries,id',
            'beneficiaries.*.full_name'  => 'required_with:beneficiaries|string|max:200',
            'beneficiaries.*.relationship' => 'nullable|string|max:50',
            'beneficiaries.*.age'        => 'nullable|string|max:10',
            'beneficiaries.*.auxilio'    => 'nullable|string|max:50',
            'beneficiaries.*.is_active'  => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $affiliate->update([
                'full_name'        => $validated['full_name'],
                'id_number'        => $validated['id_number'],
                'birth_date'       => $validated['birth_date'] ?? null,
                'contract_number'  => $validated['contract_number'] ?? null,
                'branch_id'        => $validated['branch_id'],
                'plan_id'          => $validated['plan_id'],
                'fee_amount'       => $validated['fee_amount'],
                'email'            => $validated['email'] ?? null,
                'phone'            => $validated['phone'] ?? null,
                'address'          => $validated['address'] ?? null,
                'neighborhood'     => $validated['neighborhood'] ?? null,
                'advisor_name'     => $validated['advisor_name'] ?? null,
                'affiliation_date' => $validated['affiliation_date'] ?? null,
            ]);

            // Sincronizar beneficiarios
            if (isset($validated['beneficiaries'])) {
                $incomingIds = collect($validated['beneficiaries'])->pluck('id')->filter()->toArray();

                // Inactivar los que ya no vienen (nunca se eliminan)
                $affiliate->beneficiaries()
                    ->whereNotIn('id', $incomingIds)
                    ->update(['is_active' => false]);

                foreach ($validated['beneficiaries'] as $ben) {
                    if (!empty($ben['id'])) {
                        // Actualizar existente
                        $affiliate->beneficiaries()->where('id', $ben['id'])->update([
                            'full_name'    => $ben['full_name'],
                            'relationship' => $ben['relationship'] ?? null,
                            'age'          => $ben['age'] ?? null,
                            'auxilio'      => $ben['auxilio'] ?? null,
                            'is_active'    => $ben['is_active'] ?? true,
                        ]);
                    } else {
                        // Crear nuevo
                        $affiliate->beneficiaries()->create([
                            'full_name'    => $ben['full_name'],
                            'relationship' => $ben['relationship'] ?? null,
                            'age'          => $ben['age'] ?? null,
                            'auxilio'      => $ben['auxilio'] ?? null,
                            'is_active'    => true,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('affiliates.show', $affiliate)
                ->with('message', 'Afiliado actualizado correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al actualizar afiliado: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Ocurrió un error al actualizar.');
        }
    }

    // ── TOGGLE STATUS (inactivar/activar) ─────────────────────
    // Nunca se eliminan — se inactivan
    public function toggleStatus(Affiliate $affiliate)
    {
        $affiliate->toggleStatus();

        $msg = $affiliate->is_active
            ? "Afiliado {$affiliate->full_name} activado."
            : "Afiliado {$affiliate->full_name} inactivado.";

        return back()->with('message', $msg);
    }

    // ── IMPORT ────────────────────────────────────────────────
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            DB::beginTransaction();
            Excel::import(new AffiliatesImport, $request->file('excel_file'));
            DB::commit();

            return redirect()
                ->route('affiliates.index')
                ->with('message', 'Importación completada correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error en importación: ' . $e->getMessage());
            return back()->with('error', 'Error durante la importación. Revisa el archivo.');
        }
    }
}