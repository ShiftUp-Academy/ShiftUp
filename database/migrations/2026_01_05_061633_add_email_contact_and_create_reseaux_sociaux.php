<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('ProfilsUtilisateurs', function (Blueprint $table) {
            $table->string('EmailContact', 100)->nullable()->after('Metier');
        });

        Schema::create('ReseauxSociaux', function (Blueprint $table) {
            $table->id('IdReseauSocial');
            $table->foreignId('IdUtilisateur')->constrained('ProfilsUtilisateurs', 'IdProfil')->onDelete('cascade');
            $table->string('Nom', 30);
            $table->text('Lien');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ReseauxSociaux');
        Schema::table('ProfilsUtilisateurs', function (Blueprint $table) {
            $table->dropColumn('EmailContact');
        });
    }
};
