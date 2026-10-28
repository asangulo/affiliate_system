<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Usuario de acceso de los titulares (ej. alex.angulo). Los demás entran con correo.
            $table->string('username', 60)->nullable()->unique()->after('email');
            // Fuerza a cambiar la contraseña inicial en el primer ingreso.
            $table->boolean('must_change_password')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'must_change_password']);
        });
    }
};
