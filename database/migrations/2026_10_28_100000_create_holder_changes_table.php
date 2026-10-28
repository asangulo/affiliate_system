<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de cambios de titular de un contrato (fallecimiento del titular).
 * Guarda una "foto" del titular anterior; el contrato (pagos, plan, cuota)
 * sigue siendo el mismo registro de affiliates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holder_changes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();

            $table->string('reason', 30)->default('fallecimiento');
            $table->date('deceased_at');
            $table->text('notes')->nullable();

            // Titular anterior (fallecido)
            $table->string('previous_full_name');
            $table->string('previous_id_number', 20);
            $table->date('previous_birth_date')->nullable();
            $table->string('previous_phone')->nullable();
            $table->string('previous_email')->nullable();

            // Nuevo titular (null si el contrato se cerró sin sucesor)
            $table->foreignId('beneficiary_id')->nullable()->constrained('beneficiaries')->nullOnDelete();
            $table->string('new_full_name')->nullable();
            $table->string('new_id_number', 20)->nullable();

            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holder_changes');
    }
};
