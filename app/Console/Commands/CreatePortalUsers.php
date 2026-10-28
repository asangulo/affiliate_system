<?php

namespace App\Console\Commands;

use App\Models\Affiliate;
use App\Services\PortalAccountService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreatePortalUsers extends Command
{
    protected $signature = 'portal:create-users {--dry-run : Muestra los usuarios que se crearían sin guardar nada}';

    protected $description = 'Crea la cuenta del portal (rol cliente) de los titulares activos que aún no tienen una';

    public function handle(PortalAccountService $service): int
    {
        $dry = $this->option('dry-run');
        $creados = 0;

        DB::beginTransaction();

        Affiliate::active()->doesntHave('user')->chunkById(200, function ($afiliados) use ($service, &$creados) {
            foreach ($afiliados as $aff) {
                $user = $service->ensureFor($aff);

                if ($user) {
                    $creados++;
                    $this->line("{$user->username}  <-  {$aff->full_name} (C.C. {$aff->id_number})");
                } else {
                    $this->warn("Sin usuario: {$aff->full_name} (C.C. {$aff->id_number}) — nombre vacío o inválido");
                }
            }
        });

        $dry ? DB::rollBack() : DB::commit();

        $this->info(($dry ? '[Simulación] Se crearían ' : 'Usuarios creados: ') . $creados);

        return self::SUCCESS;
    }
}
