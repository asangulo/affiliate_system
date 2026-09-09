<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'code')) {
                $table->string('code', 20)->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('branches', 'city')) {
                $table->string('city')->nullable()->after('code');
            }
            if (!Schema::hasColumn('branches', 'department')) {
                $table->string('department')->nullable()->after('city');
            }
            if (!Schema::hasColumn('branches', 'address')) {
                $table->string('address')->nullable()->after('department');
            }
            if (!Schema::hasColumn('branches', 'phone')) {
                $table->string('phone', 40)->nullable()->after('address');
            }
            if (!Schema::hasColumn('branches', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('branches', 'manager_name')) {
                $table->string('manager_name')->nullable()->after('email');
            }
            if (!Schema::hasColumn('branches', 'opened_at')) {
                $table->date('opened_at')->nullable()->after('manager_name');
            }
            if (!Schema::hasColumn('branches', 'notes')) {
                $table->text('notes')->nullable()->after('opened_at');
            }
            if (!Schema::hasColumn('branches', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'city', 'department', 'address', 'phone',
                'email', 'manager_name', 'opened_at', 'notes', 'is_active',
            ]);
        });
    }
};
