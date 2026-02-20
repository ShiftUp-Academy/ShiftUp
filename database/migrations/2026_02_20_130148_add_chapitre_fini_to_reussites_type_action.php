<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to handle the enum change carefully if it's a constraint
        // Usually, recreate the column with the new set of values
        \DB::statement("ALTER TABLE reussites DROP CONSTRAINT IF EXISTS reussites_type_action_check");
        \DB::statement("ALTER TABLE reussites ADD CONSTRAINT reussites_type_action_check CHECK (type_action IN ('lecon_terminee', 'etape_passee', 'seuil_points', 'reservation_evenement', 'temoignage_laisse', 'offre_achetee', 'chapitre_fini', 'autre'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reussites_type_action', function (Blueprint $table) {
            //
        });
    }
};
