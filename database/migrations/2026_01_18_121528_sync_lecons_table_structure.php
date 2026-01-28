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
        Schema::table('Lecons', function (Blueprint $table) {
            if (Schema::hasColumn('Lecons', 'LienPhoto')) {
                $table->dropColumn('LienPhoto');
            }
            
            // Re-align types and constraints
            $table->string('Statut', 50)->default('Dépublié')->change();
            $table->string('TypeLecon', 255)->change(); // Checks will be handled at app or DB level via check constraints if supported
            $table->string('SourceType', 255)->change();
            $table->text('Contenu')->nullable(false)->change();
            $table->integer('DureeVideo')->nullable()->change();
            $table->integer('NombreDePages')->nullable()->change();
            $table->integer('Ordre')->default(0)->change();
            $table->integer('PointsOfferts')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Lecons', function (Blueprint $table) {
            $table->text('LienPhoto')->nullable();
        });
    }
};
