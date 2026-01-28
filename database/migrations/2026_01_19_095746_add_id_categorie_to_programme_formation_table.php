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
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            // Add nullable IdCategorie column if it doesn't exist
            if (!Schema::hasColumn('ProgrammeFormation', 'IdCategorie')) {
                $table->unsignedBigInteger('IdCategorie')->nullable();
                
                // Add foreign key constraint
                $table->foreign('IdCategorie')
                      ->references('IdCategorie')
                      ->on('Categories')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            $table->dropForeign(['IdCategorie']);
            $table->dropColumn('IdCategorie');
        });
    }
};
