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
        Schema::create('Utilisateurs', function (Blueprint $table) {
            $table->id('IdUtilisateur');
            $table->string('Email')->unique();
            $table->string('MotDePasseHash')->nullable();
            $table->string('GoogleId')->unique()->nullable();
            $table->enum('Role', ['utilisateur', 'admin'])->default('utilisateur');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent();
        });

        Schema::create('ProfilsUtilisateurs', function (Blueprint $table) {
            $table->id('IdProfil');
            $table->foreignId('IdUtilisateur')->unique()->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->string('Prenom', 100)->nullable();
            $table->string('Nom', 100)->nullable();
            $table->string('Metier', 150)->nullable();
            $table->text('PhotoProfil')->nullable();
            $table->string('NumeroTelephone', 50)->nullable();
            $table->text('Adresse')->nullable();
            $table->text('Biographie')->nullable();
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ProfilsUtilisateurs');
        Schema::dropIfExists('Utilisateurs');
    }
};
