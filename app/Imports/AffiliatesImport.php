<?php
// app/Imports/AffiliatesImport.php

namespace App\Imports;

use App\Models\Affiliate;
use App\Models\Beneficiary;
use App\Models\Branch;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AffiliatesImport implements OnEachRow, WithHeadingRow
{
    // ── Columna de comentarios ────────────────────────────────────────────────
    private string $commentColumn = 'R';

    // ── Relaciones en ORDEN (las más largas primero para evitar solapamientos) ─
    private array $relaciones = [
        'HIJASTRO', 'HIJASTRA',
        'BISABUELO', 'BISABUELA',
        'COMPAÑERO', 'COMPAÑERA', 'COMPANERO', 'COMPANERA',
        'ESPOSO', 'ESPOSA',
        'HERMANO', 'HERMANA',
        'SOBRINO', 'SOBRINA',
        'SUEGRO', 'SUEGRA',
        'ABUELO', 'ABUELA',
        'PRIMO', 'PRIMA',
        'PADRE', 'MADRE',
        'NIETO', 'NIETA',
        'CUÑADO', 'CUÑADA',
        'EMPLEADO', 'EMPLEADA',
        'AMIGO', 'AMIGA',
        'YERNO', 'NUERA',
        'HIJO', 'HIJA',
        'MAMA', 'PAPA',
        'TIO', 'TIA',
        'PAREJA',
        'CANINO',
    ];

    // =========================================================================
    //  FILA PRINCIPAL
    // =========================================================================
    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        $data     = $row->toArray();

        // Ignorar filas sin cédula
        $rawId = trim((string)($data['cedula'] ?? ''));
        $cleanId = preg_replace('/\D/', '', $rawId);
        if (empty($cleanId)) return;

        // Sede y plan
        $branch   = Branch::firstOrCreate(['name' => 'Granada']);
        $planName = $this->cleanText($data['plan'] ?? 'BÁSICO') ?: 'BÁSICO';
        $plan     = Plan::firstOrCreate(['name' => $planName]);

        // Upsert afiliado
        $affiliate = Affiliate::firstOrNew(['id_number' => $cleanId]);
        $affiliate->fill([
            'contract_number'  => $this->cleanText($data['contrato'] ?? null),
            'full_name'        => $this->cleanText($data['nombre_afiliado'] ?? null),
            'branch_id'        => $branch->id,
            'plan_id'          => $plan->id,
            'fee_amount'       => $this->cleanMoney($data['valor_cuota'] ?? 0),
            'phone'            => $this->cleanText($data['telefono_1'] ?? null),
            'email'            => $this->cleanEmail($data['correo_electronico'] ?? null),
            'address'          => $this->cleanText($data['direccion'] ?? null),
            'neighborhood'     => $this->cleanText($data['barrio'] ?? null),
            'advisor_name'     => $this->cleanText($data['asesor'] ?? null),
            'affiliation_date' => $this->cleanDate($data['fecha_afiliacion_diamesano'] ?? null),
            'birth_date'       => $this->cleanDate($data['fecha_de_nacimiento'] ?? null),
        ]);
        $affiliate->save();

        // Leer comentario columna R y procesar beneficiarios
        $commentText = $this->getCommentText($row, $rowIndex);
        if ($commentText !== null) {
            $this->processBeneficiaries($affiliate, $commentText);
        }
    }

    // =========================================================================
    //  LEER COMENTARIO (threaded + normal)
    // =========================================================================
    private function getCommentText(Row $row, int $rowIndex): ?string
    {
        try {
            $sheet   = $row->getDelegate()->getWorksheet();
            $cellRef = $this->commentColumn . $rowIndex;
            $comment = $sheet->getComment($cellRef);

            if (!$comment) return null;

            $raw = $comment->getText()->getPlainText();

            // Eliminar prefijo de threaded comments (todo lo que va antes del contenido real)
            // El prefijo siempre termina con la URL de Microsoft y luego "Comment:" o "\n"
            $raw = preg_replace(
                '/\[Threaded comment\].*?(?=\n\s*\n|\nComment\s*:|\nNOMBRES|\nNombre)/su',
                '',
                $raw
            );

            // Eliminar líneas "Comment:" y "Reply:" que deja el formato threaded
            $raw = preg_replace('/^(Comment|Reply)\s*:\s*\n?/mu', '', $raw);

            // Eliminar la URL de Microsoft si quedó suelta
            $raw = preg_replace('/https?:\/\/go\.microsoft\.com\S*/u', '', $raw);
            $raw = preg_replace('/Your version of Excel.*?(?=\n|$)/isu', '', $raw);

            $text = trim($raw ?? '');
            return $text !== '' ? $text : null;

        } catch (\Throwable $e) {
            Log::warning('Error leyendo comentario', [
                'fila'  => $rowIndex,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // =========================================================================
    //  PROCESAR BENEFICIARIOS
    // =========================================================================
    private function processBeneficiaries(Affiliate $affiliate, string $rawText): void
    {
        // Colapsar todo a una sola línea plana (los threaded llegan sin \n entre bens)
        $flat = preg_replace('/\s+/', ' ', $rawText);
        $flat = trim($flat ?? '');

        [$beneficiaries, $holderNote] = $this->parseFlatText($flat);

        foreach ($beneficiaries as $b) {
            $ben = $affiliate->beneficiaries()->firstOrNew(['full_name' => $b['full_name']]);
            $ben->fill([
                'full_name'    => $b['full_name'],
                'relationship' => $b['relationship'],
                'age'          => $b['age'],
                'auxilio'      => $b['auxilio'],
                'holder_note'  => $holderNote,
                'is_active'    => true,
            ]);
            $ben->save();
        }

        Log::info('Beneficiarios procesados', [
            'affiliate'   => $affiliate->id_number,
            'count'       => count($beneficiaries),
            'holder_note' => $holderNote,
        ]);
    }

    // =========================================================================
    //  PARSER PLANO — funciona sobre texto sin saltos de línea
    //  Patrón: NOMBRE... EDAD PARENTESCO [AUX%] NOMBRE...
    // =========================================================================
    private function parseFlatText(string $texto): array
    {
        $textoNorm = $this->normText($texto);

        // Construir regex: EDAD RELACION [AUX%]
        $relPattern = implode('|', $this->relaciones);
        $pattern    = '/(\d{1,3}(?:\s+MESES?)?)\s+(' . $relPattern . ')(?:\s+(\d{1,3}(?:[.,]\d+)?\s*%))?/u';

        preg_match_all($pattern, $textoNorm, $matches, PREG_OFFSET_CAPTURE);

        $beneficiarios = [];
        $totalMatches  = count($matches[0]);
        $prevEnd       = 0;

        for ($i = 0; $i < $totalMatches; $i++) {
            $matchStart = $matches[0][$i][1];
            $edad       = trim($matches[1][$i][0]);
            $relacion   = trim($matches[2][$i][0]);
            $auxilio    = (isset($matches[3][$i][0]) && $matches[3][$i][0] !== '')
                          ? trim($matches[3][$i][0])
                          : null;

            // Nombre = texto entre fin del match anterior e inicio de la edad
            $nombreRaw = substr($textoNorm, $prevEnd, $matchStart - $prevEnd);
            $nombreRaw = $this->limpiarNombre($nombreRaw);

            $prevEnd = $matches[0][$i][1] + strlen($matches[0][$i][0]);

            if (mb_strlen($nombreRaw) >= 3) {
                $beneficiarios[] = [
                    'full_name'    => $this->cleanText(mb_convert_case($nombreRaw, MB_CASE_TITLE, 'UTF-8')),
                    'relationship' => $this->normalizeRelationship($relacion),
                    'age'          => $edad,
                    'auxilio'      => $auxilio,
                ];
            }
        }

        // Todo lo que quede después del último match → holder_note
        $resto      = trim(substr($textoNorm, $prevEnd));
        $holderNote = $resto !== '' ? $this->cleanText($resto) : null;

        return [$beneficiarios, $holderNote];
    }

    // =========================================================================
    //  LIMPIAR NOMBRE (quitar cédulas, encabezados, basura)
    // =========================================================================
    private function limpiarNombre(string $nombre): string
    {
        // Eliminar residuos del prefijo threaded de Excel
        $nombre = preg_replace('/your\s+version\s+of\s+excel.*$/iu', '', $nombre);
        $nombre = preg_replace('/https?:\/\/go\.microsoft\.com\S*/iu', '', $nombre);
        $nombre = preg_replace('/\[threaded\s+comment\].*$/iu', '', $nombre);
        // Cédulas con puntos: 39.633.792
        $nombre = preg_replace('/\b\d{1,3}(?:[.\-]\d{3})+\b/', '', $nombre) ?? $nombre;
        // Cédulas sin puntos (7-12 dígitos)
        $nombre = preg_replace('/\b\d{7,12}\b/', '', $nombre) ?? $nombre;
        // "CC." o "C.C."
        $nombre = preg_replace('/\bC\.?C\.?\s*/u', '', $nombre) ?? $nombre;
        // Encabezados frecuentes al inicio
        $nombre = preg_replace(
            '/^(NOMBRE\s+Y\s+APELLIDOS?\s*|NOMBRE\s+DOC\.?\s*IDENTIDAD\s*EDAD\s*PARENTESCO\s*(?:AUXILIO%?|AUX%?)?\s*|NOMBRE\s+EDAD\s*PARENTESCO\s*(?:AUXILIO%?|AUX%?)?\s*|NOMBRES\s+EDAD\s*PARENTESCO\s*(?:AUXILIO%?|AUX%?)?\s*|NOMBRES?\s*)/ui',
            '',
            $nombre
        ) ?? $nombre;
        return trim(preg_replace('/\s+/u', ' ', $nombre) ?? $nombre);
    }

    // =========================================================================
    //  NORMALIZAR TEXTO (quitar tildes, mayúsculas)
    // =========================================================================
    private function normText(string $text): string
    {
        $text = mb_strtoupper(trim($text), 'UTF-8');
        $from = ['Á','É','Í','Ó','Ú','Ñ','á','é','í','ó','ú','ñ'];
        $to   = ['A','E','I','O','U','N','A','E','I','O','U','N'];
        return str_replace($from, $to, $text);
    }

    private function normalizeRelationship(string $r): string
    {
        return mb_convert_case(strtolower($r), MB_CASE_TITLE, 'UTF-8');
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================
    private function cleanText(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim(preg_replace('/\s+/u', ' ', $v) ?? $v);
        return $v !== '' ? $v : null;
    }

    private function cleanEmail(?string $v): ?string
    {
        $clean = $this->cleanText($v);
        if ($clean === null || strtoupper($clean) === 'NO') return null;
        return filter_var($clean, FILTER_VALIDATE_EMAIL) ? $clean : null;
    }

    private function cleanMoney(mixed $value): string
    {
        $n = preg_replace('/[^0-9.]/', '', (string)$value) ?? '0';
        return number_format((float)$n, 2, '.', '');
    }

    private function cleanDate(mixed $value): ?string
    {
        if ($value === null || $value === '' || strtoupper((string)$value) === 'NO') return null;
        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject((float)$value)->format('Y-m-d');
            }
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    // =========================================================================
    //  CONFIGURACIÓN MAATWEBSITE
    // =========================================================================
    public function headingRow(): int
    {
        return 1; // encabezados en fila 1
    }
}