<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanExpiredAvailabilities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'availabilities:clean
                            {--days=0 : Nombre de jours supplémentaires à conserver après expiration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les disponibilités de coaching dont la date est dépassée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        // Date limite : aujourd'hui moins le nombre de jours spécifiés
        $cutoffDate = Carbon::now()->subDays($days)->format('Y-m-d');

        $this->info("Nettoyage des disponibilités avant le {$cutoffDate}...");

        try {
            // Compte le nombre de disponibilités à supprimer
            $count = DB::table('disponibilites')
                ->where('DateDisponible', '<', $cutoffDate)
                ->count();

            if ($count === 0) {
                $this->info('Aucune disponibilité expirée à supprimer.');
                return Command::SUCCESS;
            }

            // Supprime les disponibilités expirées
            $deleted = DB::table('disponibilites')
                ->where('DateDisponible', '<', $cutoffDate)
                ->delete();

            $this->line("✓ <fg=green>{$deleted} disponibilité(s) expirée(s) supprimée(s) avec succès.</>");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erreur lors du nettoyage : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
