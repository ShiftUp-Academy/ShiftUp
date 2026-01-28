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
        // Fix ProgrammeFormation (Needs deleted_at)
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            if (!Schema::hasColumn('ProgrammeFormation', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Fix Lecons (Needs timestamps and soft deletes)
        Schema::table('Lecons', function (Blueprint $table) {
            if (!Schema::hasColumn('Lecons', 'DateCreation')) {
                $table->timestamp('DateCreation')->useCurrent();
            }
            if (!Schema::hasColumn('Lecons', 'DateMiseAJour')) {
                $table->timestamp('DateMiseAJour')->useCurrent();
            }
            if (!Schema::hasColumn('Lecons', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Fix Etapes (Needs timestamps and soft deletes)
        Schema::table('Etapes', function (Blueprint $table) {
            if (!Schema::hasColumn('Etapes', 'DateCreation')) {
                $table->timestamp('DateCreation')->useCurrent();
            }
            if (!Schema::hasColumn('Etapes', 'DateMiseAJour')) {
                $table->timestamp('DateMiseAJour')->useCurrent();
            }
            if (!Schema::hasColumn('Etapes', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('Lecons', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });

        Schema::table('Etapes', function (Blueprint $table) {
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });
    }
};
