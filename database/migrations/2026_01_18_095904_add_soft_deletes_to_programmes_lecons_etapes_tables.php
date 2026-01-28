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
            $table->softDeletes();
        });
        
        if (Schema::hasTable('Lecons')) {
             Schema::table('Lecons', function (Blueprint $table) {
                $table->softDeletes();
            });
        } elseif (Schema::hasTable('Lecon')) {
             Schema::table('Lecon', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

         // Assuming Etapes
        if (Schema::hasTable('Etapes')) {
             Schema::table('Etapes', function (Blueprint $table) {
                $table->softDeletes();
            });
        } elseif (Schema::hasTable('Etape')) {
             Schema::table('Etape', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        
        if (Schema::hasTable('Lecons')) {
             Schema::table('Lecons', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        } elseif (Schema::hasTable('Lecon')) {
             Schema::table('Lecon', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('Etapes')) {
             Schema::table('Etapes', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        } elseif (Schema::hasTable('Etape')) {
             Schema::table('Etape', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
