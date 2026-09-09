<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('affiliate_id')
                  ->constrained('affiliates')
                  ->cascadeOnDelete();

            // Periodo que cubre el pago (una cuota = un mes)
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');

            $table->decimal('amount', 12, 2);
            $table->date('paid_at');

            $table->enum('method', [
                'efectivo', 'transferencia', 'consignacion',
                'nequi', 'daviplata', 'otro',
            ])->default('efectivo');

            $table->string('reference', 80)->nullable();
            $table->text('notes')->nullable();

            // Quién registró el pago
            $table->foreignId('registered_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Un afiliado no puede tener dos pagos del mismo periodo
            $table->unique(['affiliate_id', 'period_year', 'period_month'], 'payments_period_unique');
            $table->index(['period_year', 'period_month']);
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
