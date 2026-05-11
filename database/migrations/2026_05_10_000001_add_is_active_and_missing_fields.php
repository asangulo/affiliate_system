<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el campo is_active a affiliates y users.
 * Usamos soft-disable en lugar de delete para nunca perder registros.
 *
 * Ejecutar con: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Afiliados ────────────────────────────────────────
        Schema::table('affiliates', function (Blueprint $table) {
            // Estado activo/inactivo — nunca se eliminan registros
            if (!Schema::hasColumn('affiliates', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('affiliation_date');
            }

            // Campos faltantes que el formulario manual necesita
            if (!Schema::hasColumn('affiliates', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('affiliates', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('affiliates', 'neighborhood')) {
                $table->string('neighborhood')->nullable()->after('address');
            }
            if (!Schema::hasColumn('affiliates', 'advisor_name')) {
                $table->string('advisor_name')->nullable()->after('neighborhood');
            }

            // ya existe, solo aseguramos nullable si está presente
            if (Schema::hasColumn('affiliates', 'contract_number')) {
                $table->string('contract_number')->nullable()->change();
            }
        });

        // ── Beneficiarios ─────────────────────────────────────
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (!Schema::hasColumn('beneficiaries', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('auxilio');
            }
        });

        // ── Usuarios ──────────────────────────────────────────
        // El campo is_active en users lo maneja Spatie junto con los roles.
        // Lo agregamos aquí para poder desactivar cuentas sin borrarlas.
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('email_verified_at');
            }

            // FK al afiliado (para el rol cliente)
            if (!Schema::hasColumn('users', 'affiliate_id')) {
                $table->foreignId('affiliate_id')->nullable()->constrained('affiliates')->nullOnDelete()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $cols = ['is_active', 'birth_date', 'address', 'neighborhood', 'advisor_name'];
            $existing = array_values(array_filter($cols, fn ($c) => Schema::hasColumn('affiliates', $c)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });

        Schema::table('beneficiaries', function (Blueprint $table) {
            if (Schema::hasColumn('beneficiaries', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'affiliate_id')) {
                $table->dropForeign(['affiliate_id']);
            }

            $cols = ['is_active', 'affiliate_id'];
            $existing = array_values(array_filter($cols, fn ($c) => Schema::hasColumn('users', $c)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};