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
        Schema::create('CommandeItems', function (Blueprint $table) {
            $table->id('IdCommandeItem');
            $table->foreignId('IdCommande')->constrained('Commandes', 'IdCommande')->onDelete('cascade');
            $table->foreignId('IdProgrammeFormation')->nullable()->constrained('ProgrammeFormation', 'IdProgrammeFormation')->onDelete('SET NULL');
            $table->foreignId('IdOffre')->nullable()->constrained('Offres', 'IdOffre')->onDelete('SET NULL');
            $table->decimal('PrixAchat', 10, 2);
            $table->timestamp('DateCreation')->useCurrent();
        });

        // Add the constraint for mutual exclusivity
        DB::statement('ALTER TABLE "CommandeItems" ADD CONSTRAINT Check_Type_Item_Commande CHECK (
            ("IdProgrammeFormation" IS NOT NULL AND "IdOffre" IS NULL) OR 
            ("IdProgrammeFormation" IS NULL AND "IdOffre" IS NOT NULL)
        )');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CommandeItems');
    }
};
