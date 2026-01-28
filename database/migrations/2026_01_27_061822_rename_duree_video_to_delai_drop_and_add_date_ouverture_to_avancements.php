<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add new column DelaiDrop
        Schema::table('Lecons', function (Blueprint $table) {
            $table->integer('DelaiDrop')->nullable()->after('Contenu');
        });

        // 2. Try to migrate data from DureeVideo to DelaiDrop (if it's numeric)
        // Using raw SQL to handle potential PG cast issues
        try {
             DB::statement('UPDATE "Lecons" SET "DelaiDrop" = CAST(NULLIF("DureeVideo", \'\') AS INTEGER) WHERE "DureeVideo" ~ \'^[0-9]+$\'');
        } catch (\Exception $e) {
            // If it fails (maybe not PG or different table name), we just skip data migration
        }

        // 3. Drop old column
        Schema::table('Lecons', function (Blueprint $table) {
            $table->dropColumn('DureeVideo');
        });

        // 4. Update Avancements table
        Schema::table('Avancements', function (Blueprint $table) {
            $table->timestamp('DateOuverture')->nullable()->after('EntiteType');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Avancements', function (Blueprint $table) {
            $table->dropColumn('DateOuverture');
        });

        Schema::table('Lecons', function (Blueprint $table) {
            $table->string('DureeVideo')->nullable()->after('Contenu');
        });

        try {
            DB::statement('UPDATE "Lecons" SET "DureeVideo" = CAST("DelaiDrop" AS VARCHAR)');
        } catch (\Exception $e) {}

        Schema::table('Lecons', function (Blueprint $table) {
            $table->dropColumn('DelaiDrop');
        });
    }
};
