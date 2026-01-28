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
        Schema::table('Themes', function (Blueprint $table) {
            $table->string('Statut', 50)->default('Dépublié')->after('Descriptions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Themes', function (Blueprint $table) {
            $table->dropColumn('Statut');
        });
    }
};
