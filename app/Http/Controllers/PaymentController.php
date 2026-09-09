<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Branch;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Listado general de pagos registrados (admin / secretaría).
     */
    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with(['affiliate:id,full_name,id_number,contract_number', 'registeredBy:id,name'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('affiliate', function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('contract_number', 'like', "%{$search}%");
                });
            })
            ->when($request->year, fn ($q, $y) => $q->where('period_year', $y))
            ->when($request->month, fn ($q, $m) => $q->where('period_month', $m))
            ->when($request->method, fn ($q, $m) => $q->where('method', $m))
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $mesActual = Carbon::now();

        return Inertia::render('Payments/Index', [
            'payments' => $payments,
            'filters'  => $request->only(['search', 'year', 'month', 'method']),
            'stats'    => [
                'month_total' => (float) Payment::forPeriod($mesActual->year, $mesActual->month)->sum('amount'),
                'month_count' => Payment::forPeriod($mesActual->year, $mesActual->month)->count(),
                'year_total'  => (float) Payment::inYear($mesActual->year)->sum('amount'),
                'period'      => Affiliate::monthName($mesActual->month) . ' ' . $mesActual->year,
            ],
            'years'    => range(Carbon::now()->year, Carbon::now()->year - 5),
        ]);
    }

    /**
     * Cartera: quién está al día y quién debe.
     */
    public function portfolio(Request $request)
    {
        $affiliates = Affiliate::query()
            ->active()
            ->with(['branch:id,name', 'plan:id,name'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('id_number', 'like', "%{$search}%")
                        ->orWhere('contract_number', 'like', "%{$search}%");
                });
            })
            ->when($request->branch_id, fn ($q, $b) => $q->where('branch_id', $b))
            ->orderBy('full_name')
            ->get();

        // Calculamos la cartera en memoria (el dataset es de cientos, no millones)
        $filas = $affiliates->map(function (Affiliate $a) {
            $resumen = $a->paymentSummary();

            return [
                'id'             => $a->id,
                'full_name'      => $a->full_name,
                'id_number'      => $a->id_number,
                'contract'       => $a->contract_number,
                'branch'         => $a->branch?->name,
                'plan'           => $a->plan?->name,
                'fee_amount'     => (float) $a->fee_amount,
                'expected'       => $resumen['expected'],
                'paid'           => $resumen['paid'],
                'overdue'        => $resumen['overdue'],
                'overdue_amount' => $resumen['overdue_amount'],
                'up_to_date'     => $resumen['up_to_date'],
                'next_due'       => $resumen['next_due'],
                'last_payment'   => $resumen['last_payment'],
            ];
        });

        if ($request->status === 'overdue') {
            $filas = $filas->where('up_to_date', false)->values();
        } elseif ($request->status === 'current') {
            $filas = $filas->where('up_to_date', true)->values();
        }

        // Orden: los que más deben primero
        if ($request->sort === 'debt') {
            $filas = $filas->sortByDesc('overdue_amount')->values();
        }

        return Inertia::render('Payments/Portfolio', [
            'rows'     => $filas,
            'filters'  => $request->only(['search', 'branch_id', 'status', 'sort']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'totals'   => [
                'affiliates'   => $filas->count(),
                'overdue'      => $filas->where('up_to_date', false)->count(),
                'current'      => $filas->where('up_to_date', true)->count(),
                'debt_amount'  => round($filas->sum('overdue_amount'), 2),
            ],
        ]);
    }

    /**
     * Formulario para registrar un pago.
     */
    public function create(Request $request)
    {
        $affiliate = null;

        if ($request->affiliate_id) {
            $affiliate = Affiliate::with(['plan:id,name', 'branch:id,name'])
                                  ->findOrFail($request->affiliate_id);
            $affiliate->summary = $affiliate->paymentSummary();
        }

        return Inertia::render('Payments/Create', [
            'affiliate'  => $affiliate,
            'affiliates' => Affiliate::active()
                                     ->orderBy('full_name')
                                     ->get(['id', 'full_name', 'id_number', 'fee_amount']),
            'methods'    => $this->methods(),
            'years'      => range(Carbon::now()->year + 1, Carbon::now()->year - 5),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'affiliate_id' => ['required', 'exists:affiliates,id'],
            'period_year'  => ['required', 'integer', 'min:2000', 'max:2100'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'paid_at'      => ['required', 'date'],
            'method'       => ['required', Rule::in(array_keys($this->methods()))],
            'reference'    => ['nullable', 'string', 'max:80'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ], [
            'affiliate_id.required' => 'Selecciona el afiliado.',
            'amount.required'       => 'Indica el valor pagado.',
            'paid_at.required'      => 'Indica la fecha en que se recibió el pago.',
        ]);

        // Evitar doble registro del mismo periodo
        $existe = Payment::where('affiliate_id', $data['affiliate_id'])
                         ->where('period_year', $data['period_year'])
                         ->where('period_month', $data['period_month'])
                         ->exists();

        if ($existe) {
            return back()->withInput()->with('error',
                'Ese afiliado ya tiene registrado el pago de ese mes.');
        }

        $data['registered_by'] = Auth::id();

        Payment::create($data);

        return redirect()->route('payments.portfolio')
                         ->with('message', 'Pago registrado correctamente.');
    }

    /**
     * Registrar de una vez varias cuotas atrasadas.
     */
    public function storeBulk(Request $request)
    {
        $data = $request->validate([
            'affiliate_id' => ['required', 'exists:affiliates,id'],
            'periods'      => ['required', 'array', 'min:1'],
            'periods.*.year'  => ['required', 'integer'],
            'periods.*.month' => ['required', 'integer', 'min:1', 'max:12'],
            'paid_at'      => ['required', 'date'],
            'method'       => ['required', Rule::in(array_keys($this->methods()))],
            'reference'    => ['nullable', 'string', 'max:80'],
        ]);

        $affiliate = Affiliate::findOrFail($data['affiliate_id']);
        $creados = 0;

        foreach ($data['periods'] as $periodo) {
            $existe = Payment::where('affiliate_id', $affiliate->id)
                             ->where('period_year', $periodo['year'])
                             ->where('period_month', $periodo['month'])
                             ->exists();

            if ($existe) {
                continue;
            }

            Payment::create([
                'affiliate_id'  => $affiliate->id,
                'period_year'   => $periodo['year'],
                'period_month'  => $periodo['month'],
                'amount'        => $affiliate->fee_amount,
                'paid_at'       => $data['paid_at'],
                'method'        => $data['method'],
                'reference'     => $data['reference'] ?? null,
                'registered_by' => Auth::id(),
            ]);

            $creados++;
        }

        return redirect()->route('payments.portfolio')
                         ->with('message', "Se registraron {$creados} cuotas.");
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return back()->with('message', 'Pago anulado.');
    }

    /**
     * Detalle de un afiliado: todos sus pagos y su estado.
     */
    public function showAffiliate(Affiliate $affiliate)
    {
        $affiliate->load(['plan:id,name', 'branch:id,name']);

        return Inertia::render('Payments/Affiliate', [
            'affiliate' => $affiliate,
            'summary'   => $affiliate->paymentSummary(),
            'payments'  => $affiliate->payments()->with('registeredBy:id,name')->get(),
            'methods'   => $this->methods(),
        ]);
    }

    /**
     * Vista del cliente: solo sus propios pagos.
     */
    public function myPayments()
    {
        $user = Auth::user();

        $affiliate = Affiliate::with(['plan:id,name', 'branch:id,name'])
                              ->find($user->affiliate_id);

        if (!$affiliate) {
            return Inertia::render('Payments/MyPayments', [
                'affiliate' => null,
                'summary'   => null,
                'payments'  => [],
            ]);
        }

        return Inertia::render('Payments/MyPayments', [
            'affiliate' => $affiliate,
            'summary'   => $affiliate->paymentSummary(),
            'payments'  => $affiliate->payments()->get(),
        ]);
    }

    private function methods(): array
    {
        return [
            'efectivo'      => 'Efectivo',
            'transferencia' => 'Transferencia',
            'consignacion'  => 'Consignación',
            'nequi'         => 'Nequi',
            'daviplata'     => 'Daviplata',
            'otro'          => 'Otro',
        ];
    }
}
