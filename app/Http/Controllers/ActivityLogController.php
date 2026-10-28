<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('subject_label', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%");
                });
            })
            ->when($request->event, fn ($q, $e) => $q->where('event', $e))
            ->when($request->user_id, fn ($q, $u) => $q->where('user_id', $u))
            ->when($request->subject_type, fn ($q, $t) => $q->where('subject_type', $t))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($log) => [
                'id'            => $log->id,
                'user_name'     => $log->user_name,
                'event'         => $log->event,
                'event_label'   => $log->event_label,
                'subject_name'  => $log->subject_name,
                'subject_type'  => $log->subject_type,
                'subject_id'    => $log->subject_id,
                'subject_label' => $log->subject_label,
                'description'   => $log->description,
                'changes'       => $log->changes_list,
                'ip_address'    => $log->ip_address,
                'created_at'    => $log->created_at->toIso8601String(),
                'created_human' => $log->created_at->diffForHumans(),
            ]);

        return Inertia::render('Activity/Index', [
            'logs'    => $logs,
            'filters' => $request->only(['search', 'event', 'user_id', 'subject_type', 'from', 'to']),
            'users'   => User::orderBy('name')->get(['id', 'name']),
            'events'  => [
                'created' => 'Creación',
                'updated' => 'Edición',
                'deleted' => 'Eliminación',
                'import'  => 'Importación',
            ],
            'types'   => [
                'App\Models\Affiliate'   => 'Afiliados',
                'App\Models\Beneficiary' => 'Beneficiarios',
                'App\Models\Payment'     => 'Pagos',
                'App\Models\Branch'      => 'Sedes',
                'App\Models\Plan'        => 'Planes',
                'App\Models\User'        => 'Usuarios',
                'App\Models\HolderChange' => 'Cambios de titular',
            ],
            'stats'   => [
                'today' => ActivityLog::whereDate('created_at', Carbon::today())->count(),
                'week'  => ActivityLog::where('created_at', '>=', Carbon::now()->subWeek())->count(),
                'total' => ActivityLog::count(),
            ],
        ]);
    }

    /**
     * Historial de un registro concreto (se puede embeber en la ficha del afiliado).
     */
    public function forSubject(Request $request, string $type, int $id)
    {
        $tipos = [
            'affiliate'   => 'App\Models\Affiliate',
            'beneficiary' => 'App\Models\Beneficiary',
            'payment'     => 'App\Models\Payment',
            'branch'      => 'App\Models\Branch',
            'user'        => 'App\Models\User',
        ];

        abort_unless(isset($tipos[$type]), 404);

        $logs = ActivityLog::where('subject_type', $tipos[$type])
                           ->where('subject_id', $id)
                           ->latest()
                           ->limit(100)
                           ->get()
                           ->map(fn ($log) => [
                               'id'          => $log->id,
                               'user_name'   => $log->user_name,
                               'event_label' => $log->event_label,
                               'description' => $log->description,
                               'changes'     => $log->changes_list,
                               'created_at'  => $log->created_at->format('d/m/Y H:i'),
                           ]);

        return response()->json($logs);
    }

    /**
     * Borrado de registros antiguos para no dejar crecer la tabla sin control.
     */
    public function prune(Request $request)
    {
        $request->validate([
            'months' => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $borrados = ActivityLog::where('created_at', '<', Carbon::now()->subMonths($request->months))
                               ->delete();

        return back()->with('message', "Se eliminaron {$borrados} registros del historial.");
    }
}
