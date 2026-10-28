<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Branch;
use App\Models\HolderChange;
use App\Models\Plan;
use App\Models\User;
use App\Services\PortalAccountService;
use App\Imports\AffiliatesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
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

        $query = Affiliate::with(['branch', 'plan', 'beneficiaries', 'user:id,affiliate_id,username']);

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
            'plans'    => Plan::where('is_active', true)->orderBy('name')->get(['id', 'name', 'base_price']),
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

            $cuenta = app(PortalAccountService::class)->ensureFor($affiliate);

            DB::commit();

            return redirect()
                ->route('affiliates.index')
                ->with('message', "Afiliado {$affiliate->full_name} registrado con éxito."
                    . ($cuenta ? " Usuario del portal: {$cuenta->username}." : ''));

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear afiliado: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Ocurrió un error al guardar. Inténtalo de nuevo.');
        }
    }

    // ── SHOW ──────────────────────────────────────────────────
    // No hay una página de detalle dedicada: el listado ya muestra
    // el detalle completo de cada afiliado en un modal ("Ver").
    public function show(Affiliate $affiliate)
    {
        return redirect()->route('affiliates.index');
    }

    // ── EDIT ──────────────────────────────────────────────────
    public function edit(Affiliate $affiliate)
    {
        return Inertia::render('Affiliates/Edit', [
            'affiliate' => $affiliate->load(['beneficiaries', 'holderChanges.performer:id,name']),
            'branches'  => Branch::orderBy('name')->get(['id', 'name']),
            'plans'     => Plan::where('is_active', true)->orWhere('id', $affiliate->plan_id)->orderBy('name')->get(['id', 'name']),
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

            return redirect()->route('affiliates.edit', $affiliate)
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

    // ── FALLECIMIENTO DEL TITULAR ─────────────────────────────
    // Un beneficiario pasa a ser el nuevo titular del mismo contrato
    // (pagos, plan y cuota se conservan). El titular anterior queda
    // en holder_changes como historial. Sin sucesor, se inactiva el contrato.
    public function deceased(Request $request, Affiliate $affiliate)
    {
        if (! $affiliate->is_active) {
            return back()->with('error', 'Este afiliado ya está inactivo.');
        }

        $hasSuccessor = $request->filled('successor_id');

        $validated = $request->validate([
            'deceased_at'   => 'required|date|before_or_equal:today',
            'notes'         => 'nullable|string|max:1000',
            'successor_id'  => [
                'nullable',
                Rule::exists('beneficiaries', 'id')
                    ->where('affiliate_id', $affiliate->id)
                    ->where('is_active', true),
            ],
            'new_id_number' => [Rule::requiredIf($hasSuccessor), 'nullable', 'string', 'max:20', 'unique:affiliates,id_number'],
            'new_birth_date' => 'nullable|date|before:today',
            'new_phone'      => 'nullable|string|max:20',
            'new_email'      => 'nullable|email|max:255',
        ], [
            'deceased_at.before_or_equal' => 'La fecha de fallecimiento no puede ser futura.',
            'new_id_number.required'      => 'Indica la cédula del nuevo titular.',
            'new_id_number.unique'        => 'Ya existe un afiliado con esa cédula.',
        ]);

        try {
            $usuarioNuevo = DB::transaction(function () use ($affiliate, $validated, $hasSuccessor) {
                $successor = $hasSuccessor
                    ? $affiliate->beneficiaries()->lockForUpdate()->findOrFail($validated['successor_id'])
                    : null;

                HolderChange::create([
                    'affiliate_id'        => $affiliate->id,
                    'reason'              => 'fallecimiento',
                    'deceased_at'         => $validated['deceased_at'],
                    'notes'               => $validated['notes'] ?? null,
                    'previous_full_name'  => $affiliate->full_name,
                    'previous_id_number'  => $affiliate->id_number,
                    'previous_birth_date' => $affiliate->birth_date,
                    'previous_phone'      => $affiliate->phone,
                    'previous_email'      => $affiliate->email,
                    'beneficiary_id'      => $successor?->id,
                    'new_full_name'       => $successor?->full_name,
                    'new_id_number'       => $validated['new_id_number'] ?? null,
                    'performed_by'        => Auth::id(),
                ]);

                if ($successor) {
                    $affiliate->update([
                        'full_name'  => $successor->full_name,
                        'id_number'  => $validated['new_id_number'],
                        'birth_date' => $validated['new_birth_date'] ?? null,
                        'phone'      => $validated['new_phone'] ?? null,
                        'email'      => $validated['new_email'] ?? null,
                    ]);

                    // Ya es titular: deja de figurar como beneficiario
                    $successor->update(['is_active' => false]);
                } else {
                    $affiliate->update(['is_active' => false]);
                }

                // La cuenta del portal era del titular fallecido
                User::where('affiliate_id', $affiliate->id)
                    ->update(['is_active' => false, 'affiliate_id' => null]);

                // El nuevo titular recibe su propia cuenta del portal
                return $successor
                    ? app(PortalAccountService::class)->ensureFor($affiliate->refresh())?->username
                    : null;
            });
        } catch (\Throwable $e) {
            Log::error('Error al registrar fallecimiento: ' . $e->getMessage());
            return back()->with('error', 'No se pudo registrar el fallecimiento. Inténtalo de nuevo.');
        }

        return back()->with('message', $hasSuccessor
            ? "Fallecimiento registrado. {$affiliate->full_name} es ahora el titular."
                . ($usuarioNuevo ? " Usuario del portal: {$usuarioNuevo}." : '')
            : 'Fallecimiento registrado. El afiliado quedó inactivo.');
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