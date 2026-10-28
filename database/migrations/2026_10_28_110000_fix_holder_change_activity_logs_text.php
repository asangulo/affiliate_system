<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Los movimientos de HolderChange se guardaron al principio con el nombre
 * técnico del modelo ("Creó holderchange cambio de titular de X"). Se
 * reescriben en español, igual que los nuevos.
 */
return new class extends Migration
{
    private const SUBJECT = 'App\Models\HolderChange';
    private const PREFIX  = 'cambio de titular de ';

    public function up(): void
    {
        $verbos = ['created' => 'Creó', 'updated' => 'Editó', 'deleted' => 'Eliminó'];

        DB::table('activity_logs')
            ->where('subject_type', self::SUBJECT)
            ->orderBy('id')
            ->each(function ($log) use ($verbos) {
                $nombre = Str::after($log->subject_label ?? '', self::PREFIX);
                $verbo  = $verbos[$log->event] ?? ucfirst($log->event);

                DB::table('activity_logs')->where('id', $log->id)->update([
                    'subject_label' => $nombre,
                    'description'   => "{$verbo} el cambio de titular de {$nombre}",
                ]);
            });
    }

    public function down(): void
    {
        // Cambio solo de texto: no se revierte.
    }
};
