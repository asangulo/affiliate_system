<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Branch;
use App\Models\Plan;
use App\Imports\AffiliatesImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AffiliateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
        $branchId = $request->input('branch_id');
        $planId = $request->input('plan_id');

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $sortableColumns = [
            'full_name',
            'id_number',
            'contract_number',
            'affiliation_date',
            'created_at',
        ];

        if (!in_array($sortBy, $sortableColumns, true)) {
            $sortBy = 'created_at';
        }

        // 1. Obtener los afiliados con sus relaciones, Usaremos query() para optimizar la consulta.
        $query = Affiliate::with(['branch', 'plan', 'beneficiaries']);

        // 2. Filtro de busqueda dinamico (Nombre o Cédula)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('id_number', 'like', "%{$search}%")
                    ->orWhere('contract_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $planId);
        }

        $query->orderBy($sortBy, $sortDirection);

        // 3. retornar los afiliados con vista Vue (inertia)

        return Inertia::render('Affiliates/Index', [
            'affiliates' => $query->paginate($perPage)->withQueryString(),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'plans' => Plan::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['search', 'sort_by', 'sort_direction', 'per_page', 'branch_id', 'plan_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Enviamos las sedes y planes para llenar los selects del formulario
        return Inertia::render('Affiliates/Create', [
            'branches' => Branch::all(['id', 'name']),
            'plans' => Plan::all(['id', 'name']),   
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'full_name' => 'required|string|max:255',
        'id_number' => 'required|string|unique:affiliates,id_number',
        'branch_id' => 'required|exists:branches,id',
        'plan_id' => 'required|exists:plans,id',
        'fee_amount' => 'required|numeric',
        'email' => 'nullable|email',
        'phone' => 'nullable|string',
    ]);

    Affiliate::create($validated);

    return redirect()->route('affiliates.index')->with('message', 'Afiliado creado con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show(Affiliate $affiliate)
    {
        return Inertia::render('Affiliates/Show', [
            'affiliate' => $affiliate->load(['branch', 'plan', 'beneficiaries']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Affiliate $affiliate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Affiliate $affiliate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Affiliate $affiliate)
    {
        //
    }

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

            Log::error('Error en importación de afiliados: ' . $e->getMessage());

            return back()->with(
                'error',
                'Ocurrió un error durante la importación. Revisa el archivo o contacta al administrador.'
            );
        }
    }

    public function storeManual(Request $request)
    {
        // Ejemplo de uso para un registro manual o edición rápida
        $affiliate = Affiliate::updateOrCreate(
            ['id_number' => $request->id_number], // Busca por cédula
            [
                'full_name'       => $request->full_name,
                'contract_number' => $request->contract_number,
                'branch_id'       => $request->branch_id,
                'plan_id'         => $request->plan_id,
                'phone'           => $request->phone,
                'email'           => $request->email,
            ]
        );

        return back()->with('message', 'Afiliado procesado correctamente');
    }
}
