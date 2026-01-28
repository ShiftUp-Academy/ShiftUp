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
            $table->string('ModaliteSeminaire', 50)->default('Présentiel')->after('LieuSeminaire');
            $table->text('LienGoogleMeet')->nullable()->after('ModaliteSeminaire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            $table->dropColumn(['ModaliteSeminaire', 'LienGoogleMeet']);
        });
    }
};
