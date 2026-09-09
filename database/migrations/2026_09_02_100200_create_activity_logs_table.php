<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Guardamos el nombre por si el usuario se elimina después
            $table->string('user_name')->nullable();

            // created | updated | deleted | restored | login | logout | import | export
            $table->string('event', 30);

            // App\Models\Affiliate
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            // "Afiliado JUAN PEREZ"
            $table->string('subject_label')->nullable();

            // Texto legible: "Creó el afiliado JUAN PEREZ"
            $table->string('description');

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
