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
        Schema::table('DisponibiliteCoaching', function (Blueprint $table) {
            if (!Schema::hasColumn('DisponibiliteCoaching', 'BlockedByReservationId')) {
                $table->unsignedBigInteger('BlockedByReservationId')->nullable();
                $table->foreign('BlockedByReservationId')
                      ->references('IdReservation')
                      ->on('ReservationCoaching')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('DisponibiliteCoaching', function (Blueprint $table) {
            if (Schema::hasColumn('DisponibiliteCoaching', 'BlockedByReservationId')) {
                $table->dropForeign(['BlockedByReservationId']);
                $table->dropColumn('BlockedByReservationId');
            }
        });
    }
};
