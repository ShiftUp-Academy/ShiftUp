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
        Schema::create('Avancements', function (Blueprint $table) {
            $table->id('IdAvancement');
            $table->foreignId('IdUtilisateur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            
            // Polymorphic relation columns
            $table->unsignedBigInteger('EntiteId');
            $table->string('EntiteType'); // e.g., 'App\Models\Lecon'
            
            $table->boolean('EstTermine')->default(false);
            $table->timestamp('DateCompletion')->nullable();
            
            $table->timestamps();
            
            // Ensure a user has only one progress record per entity
            $table->unique(['IdUtilisateur', 'EntiteId', 'EntiteType']);
            $table->index(['EntiteId', 'EntiteType']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Avancements');
    }
};
