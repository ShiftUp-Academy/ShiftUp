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
            $table->string('TitreReplay')->nullable();
            $table->text('DescriptionReplay')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ReservationCoaching', function (Blueprint $table) {
            //
        });
    }
};
