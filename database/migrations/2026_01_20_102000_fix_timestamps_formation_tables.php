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
        // Themes: Rename created_at -> DateCreation, updated_at -> DateMiseAJour
        Schema::table('Themes', function (Blueprint $table) {
            // Check for lowercase first (Postgres default)
            if (Schema::hasColumn('Themes', 'created_at') && !Schema::hasColumn('Themes', 'DateCreation')) {
                $table->renameColumn('created_at', 'DateCreation');
            } elseif (Schema::hasColumn('Themes', 'Created_at') && !Schema::hasColumn('Themes', 'DateCreation')) {
                $table->renameColumn('Created_at', 'DateCreation');
            }

            if (Schema::hasColumn('Themes', 'updated_at') && !Schema::hasColumn('Themes', 'DateMiseAJour')) {
                $table->renameColumn('updated_at', 'DateMiseAJour');
            } elseif (Schema::hasColumn('Themes', 'Updated_at') && !Schema::hasColumn('Themes', 'DateMiseAJour')) {
                $table->renameColumn('Updated_at', 'DateMiseAJour');
            }
        });

        // Lecons: Rename created_at -> DateCreation, updated_at -> DateMiseAJour
        Schema::table('Lecons', function (Blueprint $table) {
            if (Schema::hasColumn('Lecons', 'created_at') && !Schema::hasColumn('Lecons', 'DateCreation')) {
                $table->renameColumn('created_at', 'DateCreation');
            }
            if (Schema::hasColumn('Lecons', 'updated_at') && !Schema::hasColumn('Lecons', 'DateMiseAJour')) {
                $table->renameColumn('updated_at', 'DateMiseAJour');
            }
        });

        // Etapes: Rename created_at -> DateCreation, updated_at -> DateMiseAJour
        Schema::table('Etapes', function (Blueprint $table) {
            if (Schema::hasColumn('Etapes', 'created_at') && !Schema::hasColumn('Etapes', 'DateCreation')) {
                $table->renameColumn('created_at', 'DateCreation');
            }
            if (Schema::hasColumn('Etapes', 'updated_at') && !Schema::hasColumn('Etapes', 'DateMiseAJour')) {
                $table->renameColumn('updated_at', 'DateMiseAJour');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Themes', function (Blueprint $table) {
            if (Schema::hasColumn('Themes', 'DateCreation') && !Schema::hasColumn('Themes', 'Created_at')) {
                $table->renameColumn('DateCreation', 'Created_at');
            }
            if (Schema::hasColumn('Themes', 'DateMiseAJour') && !Schema::hasColumn('Themes', 'Updated_at')) {
                $table->renameColumn('DateMiseAJour', 'Updated_at');
            }
        });

        Schema::table('Lecons', function (Blueprint $table) {
            if (Schema::hasColumn('Lecons', 'DateCreation') && !Schema::hasColumn('Lecons', 'created_at')) {
                $table->renameColumn('DateCreation', 'created_at');
            }
            if (Schema::hasColumn('Lecons', 'DateMiseAJour') && !Schema::hasColumn('Lecons', 'updated_at')) {
                $table->renameColumn('DateMiseAJour', 'updated_at');
            }
        });

        Schema::table('Etapes', function (Blueprint $table) {
            if (Schema::hasColumn('Etapes', 'DateCreation') && !Schema::hasColumn('Etapes', 'created_at')) {
                $table->renameColumn('DateCreation', 'created_at');
            }
            if (Schema::hasColumn('Etapes', 'DateMiseAJour') && !Schema::hasColumn('Etapes', 'updated_at')) {
                $table->renameColumn('DateMiseAJour', 'updated_at');
            }
        });
    }
};
