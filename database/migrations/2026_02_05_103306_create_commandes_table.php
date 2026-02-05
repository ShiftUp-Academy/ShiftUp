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
        Schema::create('Commandes', function (Blueprint $table) {
            $table->id('IdCommande');
            $table->foreignId('IdUtilisateur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->string('Reference', 100)->unique();
            $table->decimal('MontantTotal', 10, 2);
            $table->enum('Statut', ['En attente', 'Payé', 'Echoué', 'Remboursé'])->default('En attente');
            $table->timestamp('DateCommande')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Commandes');
    }
};
