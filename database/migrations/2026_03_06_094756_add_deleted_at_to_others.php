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
        Schema::table('Consultations', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('Offres', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('Temoignage', function (Blueprint $table) { $table->softDeletes(); });
        Schema::table('TypeDeCoaching', function (Blueprint $table) { $table->softDeletes(); });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Consultations', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('Offres', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('Temoignage', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('TypeDeCoaching', function (Blueprint $table) { $table->dropSoftDeletes(); });
    }

};
