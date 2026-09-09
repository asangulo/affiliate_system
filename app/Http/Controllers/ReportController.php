<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Beneficiary;
use App\Models\Branch;
use App\Models\Payment;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $desde = $request->from
            ? Carbon::parse($request->from)
            : Carbon::now()->startOfYear();

        $hasta = $request->to
            ? Carbon::parse($request->to)
            : Carbon::now()->endOfDay();

        $branchId = $request->branch_id;

        return Inertia::render('Reports/Index', [
            'filters'      => [
                'from'      => $desde->toDateString(),
                'to'        => $hasta->toDateString(),
                'branch_id' => $branchId,
            ],
            'branches'     => Branch::orderBy('name')->get(['id', 'name']),
            'overview'     => $this->overview($branchId),
            'portfolio'    => $this->portfolio($branchId),
            'byPlan'       => $this->byPlan($branchId),
            'byBranch'     => $this->byBranch(),
            'income'       => $this->incomeByMonth($desde, $hasta, $branchId),
            'growth'       => $this->affiliationsByMonth($desde, $hasta, $branchId),
            'topAdvisors'  => $this->topAdvisors($desde, $hasta, $branchId),
            'ageBuckets'   => $this->beneficiaryAges($branchId),
        ]);
    }

    /**
     * Cifras generales.
     */
    private function overview(?int $branchId): array
    {
        $afiliados = Affiliate::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $activos = (clone $afiliados)->where('is_active', true)->count();
        $total   = (clone $afiliados)->count();

        $beneficiarios = Beneficiary::query()
            ->when($branchId, fn ($q) => $q->whereHas('affiliate',
                fn ($sub) => $sub->where('branch_id', $branchId)))
            ->where('is_active', true)
            ->count();

        $mes = Carbon::now();

        $recaudoMes = Payment::forPeriod($mes->year, $mes->month)
            ->when($branchId, fn ($q) => $q->whereHas('affiliate',
                fn ($sub) => $sub->where('branch_id', $branchId)))
            ->sum('amount');

        // Ingreso teórico mensual si todos pagaran
        $ingresoPotencial = (clone $afiliados)->where('is_active', true)->sum('fee_amount');

        $nuevosMes = (clone $afiliados)
            ->whereYear('affiliation_date', $mes->year)
            ->whereMonth('affiliation_date', $mes->month)
            ->count();

        return [
            'affiliates_total'   => $total,
            'affiliates_active'  => $activos,
            'affiliates_inactive'=> $total - $activos,
            'beneficiaries'      => $beneficiarios,
            'people_covered'     => $activos + $beneficiarios,
            'new_this_month'     => $nuevosMes,
            'income_month'       => round((float) $recaudoMes, 2),
            'income_potential'   => round((float) $ingresoPotencial, 2),
            'collection_rate'    => $ingresoPotencial > 0
                ? round(($recaudoMes / $ingresoPotencial) * 100, 1)
                : 0,
            'month_label'        => Affiliate::monthName($mes->month) . ' ' . $mes->year,
        ];
    }

    /**
     * Cartera: al día vs en mora. Se calcula en memoria.
     */
    private function portfolio(?int $branchId): array
    {
        $afiliados = Affiliate::active()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->withCount('payments')
            ->get(['id', 'full_name', 'fee_amount', 'affiliation_date', 'created_at', 'branch_id']);

        $alDia = 0;
        $enMora = 0;
        $deuda = 0.0;
        $morosos = [];

        foreach ($afiliados as $a) {
            $esperadas = $a->expectedInstallments();
            $pagadas   = $a->payments_count;
            $mora      = max(0, $esperadas - $pagadas);

            if ($mora === 0) {
                $alDia++;
                continue;
            }

            $enMora++;
            $monto = $mora * (float) $a->fee_amount;
            $deuda += $monto;

            $morosos[] = [
                'id'        => $a->id,
                'full_name' => $a->full_name,
                'months'    => $mora,
                'amount'    => round($monto, 2),
            ];
        }

        usort($morosos, fn ($x, $y) => $y['amount'] <=> $x['amount']);

        return [
            'up_to_date'  => $alDia,
            'overdue'     => $enMora,
            'debt_total'  => round($deuda, 2),
            'top_debtors' => array_slice($morosos, 0, 10),
        ];
    }

    /**
     * Afiliados e ingreso potencial por plan.
     */
    private function byPlan(?int $branchId): array
    {
        return Plan::query()
            ->withCount(['affiliates' => function ($q) use ($branchId) {
                $q->where('is_active', true)
                  ->when($branchId, fn ($sub) => $sub->where('branch_id', $branchId));
            }])
            ->get()
            ->map(function ($plan) use ($branchId) {
                $ingreso = Affiliate::where('plan_id', $plan->id)
                    ->where('is_active', true)
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->sum('fee_amount');

                return [
                    'name'      => $plan->name,
                    'count'     => $plan->affiliates_count,
                    'income'    => round((float) $ingreso, 2),
                    'avg_fee'   => $plan->affiliates_count > 0
                        ? round($ingreso / $plan->affiliates_count, 2)
                        : 0,
                ];
            })
            ->filter(fn ($row) => $row['count'] > 0)
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    private function byBranch(): array
    {
        return Branch::query()
            ->withCount(['affiliates' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(function ($branch) {
                $ingreso = Affiliate::where('branch_id', $branch->id)
                                    ->where('is_active', true)
                                    ->sum('fee_amount');

                return [
                    'name'   => $branch->name,
                    'count'  => $branch->affiliates_count,
                    'income' => round((float) $ingreso, 2),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * Recaudo real mes a mes.
     */
    private function incomeByMonth(Carbon $desde, Carbon $hasta, ?int $branchId): array
    {
        $filas = Payment::query()
            ->when($branchId, fn ($q) => $q->whereHas('affiliate',
                fn ($sub) => $sub->where('branch_id', $branchId)))
            ->whereBetween('paid_at', [$desde, $hasta])
            ->select(
                DB::raw('YEAR(paid_at) as anio'),
                DB::raw('MONTH(paid_at) as mes'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        return $filas->map(fn ($f) => [
            'label'  => Affiliate::monthName((int) $f->mes) . ' ' . $f->anio,
            'short'  => substr(Affiliate::monthName((int) $f->mes), 0, 3),
            'year'   => (int) $f->anio,
            'month'  => (int) $f->mes,
            'total'  => round((float) $f->total, 2),
            'count'  => (int) $f->cantidad,
        ])->all();
    }

    /**
     * Afiliaciones nuevas mes a mes.
     */
    private function affiliationsByMonth(Carbon $desde, Carbon $hasta, ?int $branchId): array
    {
        $filas = Affiliate::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('affiliation_date')
            ->whereBetween('affiliation_date', [$desde, $hasta])
            ->select(
                DB::raw('YEAR(affiliation_date) as anio'),
                DB::raw('MONTH(affiliation_date) as mes'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->groupBy('anio', 'mes')
            ->orderBy('anio')
            ->orderBy('mes')
            ->get();

        return $filas->map(fn ($f) => [
            'label' => Affiliate::monthName((int) $f->mes) . ' ' . $f->anio,
            'short' => substr(Affiliate::monthName((int) $f->mes), 0, 3),
            'count' => (int) $f->cantidad,
        ])->all();
    }

    private function topAdvisors(Carbon $desde, Carbon $hasta, ?int $branchId): array
    {
        return Affiliate::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('advisor_name')
            ->where('advisor_name', '!=', '')
            ->whereBetween('affiliation_date', [$desde, $hasta])
            ->select('advisor_name',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(fee_amount) as ingreso'))
            ->groupBy('advisor_name')
            ->orderByDesc('cantidad')
            ->limit(10)
            ->get()
            ->map(fn ($f) => [
                'name'   => $f->advisor_name,
                'count'  => (int) $f->cantidad,
                'income' => round((float) $f->ingreso, 2),
            ])
            ->all();
    }

    /**
     * Distribución de edades de los beneficiarios (útil para riesgo).
     */
    private function beneficiaryAges(?int $branchId): array
    {
        $edades = Beneficiary::query()
            ->when($branchId, fn ($q) => $q->whereHas('affiliate',
                fn ($sub) => $sub->where('branch_id', $branchId)))
            ->where('is_active', true)
            ->whereNotNull('age')
            ->pluck('age');

        $buckets = [
            '0-17'  => 0,
            '18-30' => 0,
            '31-45' => 0,
            '46-60' => 0,
            '61-75' => 0,
            '76+'   => 0,
        ];

        foreach ($edades as $edad) {
            // Las edades pueden venir como "8 MESES"
            if (stripos((string) $edad, 'mes') !== false) {
                $buckets['0-17']++;
                continue;
            }

            $n = (int) preg_replace('/\D/', '', (string) $edad);
            if ($n <= 0) continue;

            if ($n <= 17)      $buckets['0-17']++;
            elseif ($n <= 30)  $buckets['18-30']++;
            elseif ($n <= 45)  $buckets['31-45']++;
            elseif ($n <= 60)  $buckets['46-60']++;
            elseif ($n <= 75)  $buckets['61-75']++;
            else               $buckets['76+']++;
        }

        return collect($buckets)
            ->map(fn ($count, $range) => ['range' => $range, 'count' => $count])
            ->values()
            ->all();
    }
}
