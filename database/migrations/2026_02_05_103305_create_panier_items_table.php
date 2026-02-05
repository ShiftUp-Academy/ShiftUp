<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('PanierItems', function (Blueprint $table) {
            $table->id('IdPanierItem');
            $table->foreignId('IdPanier')->constrained('Paniers', 'IdPanier')->onDelete('cascade');
            $table->foreignId('IdProgrammeFormation')->nullable()->constrained('ProgrammeFormation', 'IdProgrammeFormation')->onDelete('cascade');
            $table->foreignId('IdOffre')->nullable()->constrained('Offres', 'IdOffre')->onDelete('cascade');
            $table->decimal('PrixAuMomentDeLAjout', 10, 2)->nullable();
            $table->timestamp('DateAjout')->useCurrent();
        });

        // Add the constraint for mutual exclusivity
        DB::statement('ALTER TABLE "PanierItems" ADD CONSTRAINT Check_Type_Article CHECK (
            ("IdProgrammeFormation" IS NOT NULL AND "IdOffre" IS NULL) OR 
            ("IdProgrammeFormation" IS NULL AND "IdOffre" IS NOT NULL)
        )');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PanierItems');
    }
};
