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
        Schema::create('Lives', function (Blueprint $table) {
            $table->id('IdLive');
            $table->string('Titre', 155);
            $table->foreignId('IdCategorie')->nullable()->constrained('Categories', 'IdCategorie')->onDelete('set null');
            $table->text('Descriptions')->nullable();
            $table->text('LienPhoto')->nullable();
            $table->dateTime('DateDebut');
            $table->dateTime('DateFin');
            $table->text('LienGoogleMeet')->nullable();
            $table->text('LienReplay')->nullable();
            $table->foreignId('IdAuteur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->string('Statut', 50)->default('Dépublié');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Lives');
    }
};
