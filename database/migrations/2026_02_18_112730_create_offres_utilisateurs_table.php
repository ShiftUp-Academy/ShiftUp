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
        Schema::create('OffresUtilisateurs', function (Blueprint $table) {
            $table->id('IdOffreUtilisateur');
            $table->foreignId('IdOffre')->constrained('Offres', 'IdOffre')->onDelete('cascade');
            $table->foreignId('IdUtilisateur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->timestamp('DateAttribution')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('OffresUtilisateurs');
    }
};
