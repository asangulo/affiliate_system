<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Crea la cuenta del portal (rol cliente) de cada titular.
 * Usuario: primer nombre + "." + primer apellido (ej. alex.angulo).
 */
class PortalAccountService
{
    private const PARTICULAS = ['de', 'del', 'la', 'las', 'los', 'y', 'e'];

    /**
     * Crea la cuenta si el afiliado está activo y todavía no tiene una.
     * Devuelve el usuario creado, o null si no hacía falta crear nada.
     */
    public function ensureFor(Affiliate $affiliate): ?User
    {
        // is_active puede venir sin cargar en un afiliado recién creado: solo se descarta si es false.
        if ($affiliate->is_active === false || $affiliate->user()->exists()) {
            return null;
        }

        $base = self::usernameBase((string) $affiliate->full_name);
        if ($base === null) {
            return null;
        }

        return DB::transaction(function () use ($affiliate, $base) {
            $username = $this->uniqueUsername($base);

            $user = User::create([
                'name'                 => $affiliate->full_name,
                'username'             => $username,
                'email'                => $this->emailFor($affiliate, $username),
                'password'             => config('portal.default_password'),
                'is_active'            => true,
                'must_change_password' => true,
                'affiliate_id'         => $affiliate->id,
            ]);

            // El correo interno no se puede verificar: se marca como verificado.
            $user->forceFill(['email_verified_at' => now()])->save();

            Role::findOrCreate('cliente', 'web');
            $user->assignRole('cliente');

            return $user;
        });
    }

    /**
     * "ALEX SANTIAGO ANGULO GOMEZ" => "alex.angulo".
     * 1 palabra: solo esa. 2 o 3 palabras: primera + última. 4 o más: primera + tercera.
     */
    public static function usernameBase(string $fullName): ?string
    {
        $words = preg_split('/[^a-z]+/', Str::ascii(mb_strtolower($fullName)), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_values(array_filter($words, fn ($w) => ! in_array($w, self::PARTICULAS, true)));
        $n     = count($words);

        if ($n === 0) {
            return null;
        }
        if ($n === 1) {
            return $words[0];
        }

        return $words[0] . '.' . ($n >= 4 ? $words[2] : $words[$n - 1]);
    }

    private function uniqueUsername(string $base): string
    {
        $candidate = $base;
        $i = 2;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . $i++;
        }

        return $candidate;
    }

    /**
     * Usa el correo del titular si es válido y está libre; si no, uno interno.
     */
    private function emailFor(Affiliate $affiliate, string $username): string
    {
        $propio = Str::lower(trim((string) $affiliate->email));

        if ($propio !== '' && filter_var($propio, FILTER_VALIDATE_EMAIL)
            && ! User::where('email', $propio)->exists()) {
            return $propio;
        }

        return $username . '@' . config('portal.email_domain');
    }
}
