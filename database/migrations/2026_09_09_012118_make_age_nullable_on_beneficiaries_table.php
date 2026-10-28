<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * La edad del beneficiario se trata como opcional en todo el sistema
     * (formularios y controladores), pero la columna no lo permitía y
     * reventaba la creación del afiliado con un error genérico si se
     * dejaba en blanco.
     */
    public function up(): void
    {
        // MODIFY es sintaxis de MySQL; en otros motores (SQLite de los tests) se omite.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE beneficiaries MODIFY age VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE beneficiaries MODIFY age VARCHAR(255) NOT NULL DEFAULT ''");
    }
};
