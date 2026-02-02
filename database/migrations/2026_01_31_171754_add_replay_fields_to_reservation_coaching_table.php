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
        Schema::table('ReservationCoaching', function (Blueprint $table) {
            $table->string('LienVideoReplay')->nullable();
            $table->string('StatutReplay')->default('Dépublié'); // Publié, Dépublié
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ReservationCoaching', function (Blueprint $table) {
            $table->dropColumn(['LienVideoReplay', 'StatutReplay']);
        });
    }
};
