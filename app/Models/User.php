<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    protected $activityExclude = ['password', 'remember_token', 'email_verified_at'];

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'is_active',
        'must_change_password',
        'affiliate_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function toggleStatus(): void
    {
        $this->update(['is_active' => ! $this->is_active]);
    }

    /**
     * A dónde debe llegar este usuario tras iniciar sesión, según su rol.
     * super_admin/empleado usan el panel interno; cliente usa su portal.
     */
    public function homeRouteName(): string
    {
        if ($this->can('Ver panel principal')) {
            return 'dashboard';
        }

        if ($this->can('Ver portal del cliente')) {
            return 'portal.index';
        }

        // Un rol sin ninguna de las dos: lo único que siempre puede abrir es su perfil.
        return 'profile.edit';
    }
}