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
            $table->string('Langue', 10)->nullable()->default('fr')->after('Titre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            $table->dropColumn('Langue');
        });
    }
};
