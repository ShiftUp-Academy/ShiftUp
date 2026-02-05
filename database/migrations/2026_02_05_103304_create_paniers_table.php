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
        Schema::create('Paniers', function (Blueprint $table) {
            $table->id('IdPanier');
            $table->foreignId('IdUtilisateur')->unique()->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->enum('Statut', ['actif', 'validé', 'abandonné'])->default('actif');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Paniers');
    }
};
