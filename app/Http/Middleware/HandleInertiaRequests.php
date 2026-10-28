<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Permission\Models\Permission;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user'  => $request->user()?->load(['affiliate.plan', 'affiliate.branch', 'affiliate.beneficiaries']),
                'roles' => $request->user()?->getRoleNames()->toArray() ?? [],
                // Nombres de todos los permisos del usuario (el super_admin los tiene todos)
                'permissions' => $request->user()
                    ? ($request->user()->hasRole('super_admin')
                        ? Permission::pluck('name')->all()
                        : $request->user()->getAllPermissions()->pluck('name')->values()->all())
                    : [],
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
