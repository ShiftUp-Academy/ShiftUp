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
        Schema::table('Consultations', function (Blueprint $table) {
            $table->unsignedBigInteger('IdLecon')->nullable();
            $table->unsignedBigInteger('IdCategorie')->nullable();
            $table->string('SourceType')->default('Formulaire'); // Types: 'Lecon', 'Formulaire'
            
            // Foreign Keys
            $table->foreign('IdLecon')->references('IdLecon')->on('Lecons')->onDelete('set null');
            $table->foreign('IdCategorie')->references('IdCategorie')->on('Categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Consultations', function (Blueprint $table) {
            $table->dropForeign(['IdLecon']);
            $table->dropForeign(['IdCategorie']);
            $table->dropColumn(['IdLecon', 'IdCategorie', 'SourceType']);
        });
    }
};
