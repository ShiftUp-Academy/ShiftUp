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
        Schema::create('Temoignage', function (Blueprint $table) {
            $table->id('IdTemoignage');
            $table->unsignedBigInteger('IdUtilisateur');
            $table->string('Type', 50);
            $table->text('ContenuTexte')->nullable();
            $table->text('CheminFichier')->nullable();
            $table->string('Statut', 50)->default('Publié');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent();

            $table->foreign('IdUtilisateur')->references('IdUtilisateur')->on('Utilisateurs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Temoignage');
    }
};
