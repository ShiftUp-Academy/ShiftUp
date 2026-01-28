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
            $table->string('Type', 50)->default('Formation')->after('IdProgrammeFormation');
            $table->date('DateSeminaire')->nullable()->after('Descriptions');
            $table->string('HeureSeminaire', 50)->nullable()->after('DateSeminaire');
            $table->string('LieuSeminaire', 255)->nullable()->after('HeureSeminaire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            $table->dropColumn(['Type', 'DateSeminaire', 'HeureSeminaire', 'LieuSeminaire']);
        });
    }
};
