<?php

namespace App\Imports;

use App\Models\Affiliate;
use App\Models\Branch;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Row; // Clase necesaria para OnEachRow
use Maatwebsite\Excel\Concerns\OnEachRow; // Interfaz correcta
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class AffiliatesImport implements OnEachRow, WithHeadingRow, WithUpserts
{
    private string $commentColumn = 'Q';

    /**
     * IMPORTANTE: El método debe llamarse exactamente onRow
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $rowData = $row->toArray(); // Convierte la fila en array usando los encabezados

        if (!isset($rowData['cedula']) || empty($rowData['cedula'])) {
            Log::debug('Fila omitida por cédula vacía.', [
                'row' => $rowIndex,
                'available_keys' => array_keys($rowData),
            ]);
            return;
        }

        $cleanIdNumber = preg_replace('/\D/', '', $rowData['cedula']);

        Log::info('Procesando fila de afiliado.', [
            'row' => $rowIndex,
            'id_number' => $cleanIdNumber,
            'contract_number' => $rowData['contrato'] ?? null,
            'comment_column' => $this->commentColumn,
        ]);

        // 1. Obtener relaciones
        $branch = Branch::firstOrCreate(['name' => 'Bogotá']);
        $planName = trim($rowData['plan'] ?? 'BASICO');
        $plan = Plan::firstOrCreate(['name' => $planName]);

        // 2. Usar el modelo Affiliate (con los campos de tu $fillable)
        $affiliate = $this->upsertAffiliate($cleanIdNumber, [
            'contract_number'   => $this->cleanText($rowData['contrato'] ?? null),
            'full_name'         => $this->cleanText($rowData['nombre_afiliado'] ?? null),
            'branch_id'         => $branch->id,
            'plan_id'           => $plan->id,
            'fee_amount'        => $this->cleanMoney($rowData['valor_cuota'] ?? 0),
            'phone'             => $this->cleanText($rowData['telefono'] ?? null),
            'email'             => $this->cleanText($rowData['correo_electronico'] ?? null),
            'affiliation_date'  => $this->cleanDate($rowData['fecha_afiliacion'] ?? null),
        ]);

        // 3. Procesar comentarios de la columna Q (Beneficiarios y observación del titular)
        $commentText = $this->getCommentText($row, $rowIndex);

        if ($commentText !== null) {
            $this->processBeneficiaries($affiliate, $commentText);
            return;
        }

        Log::warning('No se encontró comentario en la celda esperada para beneficiarios.', [
            'row' => $rowIndex,
            'affiliate_id' => $affiliate->id,
            'id_number' => $affiliate->id_number,
            'cell' => $this->commentColumn . $rowIndex,
        ]);
    }

    private function getCommentText(Row $row, int $rowIndex): ?string
    {
        $sheet = $row->getDelegate()->getWorksheet();
        $comment = $sheet->getComment($this->commentColumn . $rowIndex);
        $text = trim($comment?->getText()?->getPlainText() ?? '');

        Log::debug('Comentario leído desde Excel.', [
            'row' => $rowIndex,
            'cell' => $this->commentColumn . $rowIndex,
            'has_comment' => $text !== '',
            'comment_preview' => $this->previewText($text),
        ]);

        return $text !== '' ? $text : null;
    }

    private function processBeneficiaries($affiliate, string $text): void
    {
        [$beneficiaries, $holderNote] = $this->parseCommentText($text);

        Log::info('Resultado del parseo de comentario.', [
            'affiliate_id' => $affiliate->id,
            'id_number' => $affiliate->id_number,
            'beneficiaries_count' => count($beneficiaries),
            'holder_note' => $holderNote,
            'comment_preview' => $this->previewText($text),
        ]);

        if (empty($beneficiaries)) {
            Log::warning('No se detectaron beneficiarios en el comentario del Excel.', [
                'affiliate_id' => $affiliate->id,
                'id_number' => $affiliate->id_number,
                'comment_preview' => $this->previewText($text),
            ]);
        }

        foreach ($beneficiaries as $beneficiaryData) {
            $beneficiary = $this->upsertBeneficiary($affiliate, [
                'full_name' => $beneficiaryData['full_name'],
                'relationship' => $beneficiaryData['relationship'],
                'age' => $beneficiaryData['age'],
                'auxilio' => $beneficiaryData['auxilio'],
                'holder_note' => $holderNote,
            ]);

            Log::debug('Beneficiario importado o actualizado.', [
                'affiliate_id' => $affiliate->id,
                'id_number' => $affiliate->id_number,
                'beneficiary' => $beneficiaryData,
                'beneficiary_id' => $beneficiary->id,
            ]);
        }
    }

    private function parseCommentText(string $text): array
    {
        $beneficiaries = [];
        $noteLines = [];
        $pendingAux = null;
        $capturingNoteContinuation = false;

        foreach (preg_split('/\R/u', $text) as $rawLine) {
            $line = trim(preg_replace('/\s+/u', ' ', $rawLine));

            if ($line === '' || $this->isHeaderLine($line)) {
                if ($line !== '') {
                    Log::debug('Línea descartada por ser encabezado del comentario.', [
                        'line' => $line,
                    ]);
                }
                continue;
            }

            if ($this->isNoteLine($line) || $this->looksLikeFreeTextNote($line)) {
                Log::debug('Línea identificada como observación.', [
                    'line' => $line,
                ]);
                $noteLines[] = $this->extractNoteText($line);
                $capturingNoteContinuation = true;
                continue;
            }

            if ($this->isAuxOnlyLine($line)) {
                Log::debug('Línea identificada como AUX %.', [
                    'line' => $line,
                ]);
                if (!empty($beneficiaries) && empty($beneficiaries[array_key_last($beneficiaries)]['auxilio'])) {
                    $beneficiaries[array_key_last($beneficiaries)]['auxilio'] = $line;
                } else {
                    $pendingAux = $line;
                }
                continue;
            }

            $beneficiaryData = $this->parseBeneficiaryLine($line);

            if ($beneficiaryData !== null) {
                Log::debug('Línea convertida en beneficiario.', [
                    'line' => $line,
                    'parsed' => $beneficiaryData,
                ]);
                $capturingNoteContinuation = false;
                if ($pendingAux !== null && empty($beneficiaryData['auxilio'])) {
                    $beneficiaryData['auxilio'] = $pendingAux;
                    $pendingAux = null;
                }

                $beneficiaries[] = $beneficiaryData;
                continue;
            }

            if ($capturingNoteContinuation) {
                Log::debug('Línea agregada como continuación de observación.', [
                    'line' => $line,
                ]);
            }

            Log::debug('Línea no reconocida como beneficiario; se tratará como nota.', [
                'line' => $line,
            ]);
            $noteLines[] = $line;
        }

        $holderNote = trim(implode(' ', array_filter($noteLines)));

        return [$beneficiaries, $holderNote !== '' ? $holderNote : null];
    }

    private function parseBeneficiaryLine(string $line): ?array
    {
        $parts = preg_split('/\s+/u', $line);

        if ($parts === false || count($parts) < 3) {
            Log::debug('No se pudo parsear beneficiario por cantidad insuficiente de segmentos.', [
                'line' => $line,
                'parts' => $parts,
            ]);
            return null;
        }

        $aux = null;

        if ($this->isAuxOnlyLine(end($parts))) {
            $aux = array_pop($parts);
        }

        $ageIndex = null;
        $age = null;

        for ($i = count($parts) - 2; $i >= 1; $i--) {
            if (preg_match('/^\d{1,3}$/', $parts[$i])) {
                $ageIndex = $i;
                $age = $parts[$i];

                if (isset($parts[$i + 1]) && $this->isMonthToken($parts[$i + 1])) {
                    $age = $parts[$i] . ' ' . $parts[$i + 1];
                }

                break;
            }
        }

        if ($ageIndex === null) {
            Log::debug('No se pudo parsear beneficiario porque no se encontró una edad válida.', [
                'line' => $line,
                'parts' => $parts,
            ]);
            return null;
        }

        $relationshipParts = array_slice(
            $parts,
            isset($parts[$ageIndex + 1]) && $this->isMonthToken($parts[$ageIndex + 1]) ? $ageIndex + 2 : $ageIndex + 1
        );
        $nameParts = array_slice($parts, 0, $ageIndex);

        if (!empty($nameParts) && $this->looksLikeDocumentToken(end($nameParts))) {
            array_pop($nameParts);
        }

        $name = trim(implode(' ', $nameParts));
        $relationship = trim(implode(' ', $relationshipParts));

        if ($name === '' || $relationship === '' || !$this->isLikelyRelationship($relationship)) {
            Log::debug('No se pudo parsear beneficiario por nombre o parentesco vacío.', [
                'line' => $line,
                'name' => $name,
                'relationship' => $relationship,
            ]);
            return null;
        }

        return [
            'full_name' => $this->cleanText($name),
            'relationship' => $this->normalizeRelationship($relationship),
            'age' => $age,
            'auxilio' => $aux,
        ];
    }

    private function upsertAffiliate(string $idNumber, array $attributes): Affiliate
    {
        $affiliate = Affiliate::firstOrNew(['id_number' => $idNumber]);
        $affiliate->fill($attributes);

        if (!$affiliate->exists) {
            $affiliate->save();

            Log::info('Afiliado creado durante importación.', [
                'id_number' => $idNumber,
                'affiliate_id' => $affiliate->id,
            ]);

            return $affiliate;
        }

        if (!$affiliate->isDirty()) {
            Log::debug('Afiliado sin cambios; se omite actualización.', [
                'id_number' => $idNumber,
                'affiliate_id' => $affiliate->id,
            ]);

            return $affiliate;
        }

        $dirty = $affiliate->getDirty();
        $affiliate->save();

        Log::info('Afiliado actualizado por cambios detectados.', [
            'id_number' => $idNumber,
            'affiliate_id' => $affiliate->id,
            'dirty_fields' => array_keys($dirty),
        ]);

        return $affiliate;
    }

    private function upsertBeneficiary(Affiliate $affiliate, array $attributes)
    {
        $lookupName = $this->cleanText($attributes['full_name'] ?? null);
        $beneficiary = $affiliate->beneficiaries()->firstOrNew([
            'full_name' => $lookupName,
        ]);

        $beneficiary->fill([
            'full_name' => $lookupName,
            'relationship' => $attributes['relationship'] ?? null,
            'age' => $attributes['age'] ?? null,
            'auxilio' => $attributes['auxilio'] ?? null,
            'holder_note' => $attributes['holder_note'] ?? null,
        ]);

        if (!$beneficiary->exists) {
            $beneficiary->save();

            Log::info('Beneficiario creado durante importación.', [
                'affiliate_id' => $affiliate->id,
                'beneficiary' => $lookupName,
            ]);

            return $beneficiary;
        }

        if (!$beneficiary->isDirty()) {
            Log::debug('Beneficiario sin cambios; se omite actualización.', [
                'affiliate_id' => $affiliate->id,
                'beneficiary_id' => $beneficiary->id,
                'beneficiary' => $lookupName,
            ]);

            return $beneficiary;
        }

        $dirty = $beneficiary->getDirty();
        $beneficiary->save();

        Log::info('Beneficiario actualizado por cambios detectados.', [
            'affiliate_id' => $affiliate->id,
            'beneficiary_id' => $beneficiary->id,
            'beneficiary' => $lookupName,
            'dirty_fields' => array_keys($dirty),
        ]);

        return $beneficiary;
    }

    private function isHeaderLine(string $line): bool
    {
        $normalized = $this->normalizeLine($line);

        if ($normalized === 'BENEFICIARIOS') {
            return true;
        }

        if (
            (str_starts_with($normalized, 'NOMBRE') || str_starts_with($normalized, 'NOMBRES'))
            && str_contains($normalized, 'EDAD')
            && str_contains($normalized, 'PARENTESCO')
        ) {
            return true;
        }

        return in_array($normalized, ['NOMBRE', 'NOMBRES', 'EDAD', 'PARENTESCO', 'AUX %', 'AUX%'], true);
    }

    private function isNoteLine(string $line): bool
    {
        $normalized = $this->normalizeLine($line);

        return str_starts_with($normalized, 'OBSERVACION')
            || str_starts_with($normalized, 'NOTA')
            || str_starts_with($normalized, 'NOTAS');
    }

    private function extractNoteText(string $line): string
    {
        $normalized = $this->normalizeLine($line);

        if (
            str_starts_with($normalized, 'OBSERVACION')
            || str_starts_with($normalized, 'NOTA')
            || str_starts_with($normalized, 'NOTAS')
        ) {
            $parts = preg_split('/:\s*/u', $line, 2);

            if ($parts !== false && isset($parts[1])) {
                return trim($parts[1]);
            }
        }

        return trim($line);
    }

    private function isAuxOnlyLine(string $line): bool
    {
        return preg_match('/^\d{1,3}(?:[.,]\d+)?\s*%$/', $line) === 1;
    }

    private function looksLikeFreeTextNote(string $line): bool
    {
        $normalized = $this->normalizeLine($line);
        $notePrefixes = [
            'NO ENTIENDO',
            'LETRA HORRIBLE',
            'LOS PAGOS',
            'PAGOS PARA',
            'COBRAR ',
            'PARA LOS COBROS',
            'EL TITULAR',
            'LLAMAR ',
        ];

        foreach ($notePrefixes as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeDocumentToken(string $token): bool
    {
        return preg_match('/^\d{1,3}(?:[.\-]\d{3})+$/', $token) === 1;
    }

    private function isMonthToken(string $token): bool
    {
        return in_array($this->normalizeLine($token), ['MES', 'MESES'], true);
    }

    private function isLikelyRelationship(string $relationship): bool
    {
        $normalized = $this->normalizeLine($relationship);
        $validRelationships = [
            'ABUELA',
            'ABUELO',
            'AMIGA',
            'AMIGO',
            'CUÑADA',
            'CUÑADO',
            'ESPOSA',
            'ESPOSO',
            'FALLECIDO',
            'HERMANA',
            'HERMANO',
            'HERMNO',
            'HIJA',
            'HIIJA',
            'HIJASTRO',
            'HIJO',
            'MADRE',
            'MAMA',
            'MAMÁ',
            'NIETA',
            'NIETO',
            'NUERA',
            'PAPA',
            'PAPÁ',
            'PADRE',
            'PAREJA',
            'PRIMA',
            'PRIMO',
            'SOBRINA',
            'SOBRINO',
            'SUEGRA',
            'SUEGRO',
            'YERNO',
        ];

        return in_array($normalized, $validRelationships, true);
    }

    private function normalizeLine(string $line): string
    {
        $line = trim(mb_strtoupper($line));
        $line = strtr($line, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
        ]);
        $line = preg_replace('/\s+/u', ' ', $line) ?? $line;

        return trim($line);
    }

    private function normalizeRelationship(string $relationship): string
    {
        $normalized = $this->normalizeLine($relationship);
        $relationshipMap = [
            'HIIJA' => 'HIJA',
            'HERMNO' => 'HERMANO',
            'MAMA' => 'MAMÁ',
            'PAPA' => 'PAPÁ',
        ];

        return $relationshipMap[$normalized] ?? $relationship;
    }

    private function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        return $value !== '' ? $value : null;
    }

    private function previewText(string $text, int $limit = 250): ?string
    {
        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit) . '...';
    }

    private function cleanMoney($value): string
    {
        $normalized = preg_replace('/[^0-9.]/', '', (string) $value) ?? '0';

        return number_format((float) $normalized, 2, '.', '');
    }

    private function cleanDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $dt = ExcelDate::excelToDateTimeObject((float) $value);

                return $dt->format('Y-m-d');
            }

            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }

            $parsed = Carbon::parse($value);

            return $parsed->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function headingRow(): int {
        return 10;
    }

    public function uniqueBy() {
        return 'id_number';
    }
}