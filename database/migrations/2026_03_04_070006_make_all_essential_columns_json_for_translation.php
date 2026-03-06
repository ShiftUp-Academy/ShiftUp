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
            $table->json('Titre')->change();
            $table->json('Descriptions')->change();
        });

        Schema::table('Themes', function (Blueprint $table) {
            $table->json('Titre')->change();
            $table->json('Descriptions')->change();
        });

        Schema::table('Lecons', function (Blueprint $table) {
            $table->json('Titre')->change();
            $table->json('Descriptions')->change();
        });

        Schema::table('Etapes', function (Blueprint $table) {
            $table->json('Titre')->change();
            $table->json('Descriptions')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ProgrammeFormation', function (Blueprint $table) {
            $table->string('Titre')->change();
            $table->text('Descriptions')->change();
        });

        Schema::table('Themes', function (Blueprint $table) {
            $table->string('Titre')->change();
            $table->text('Descriptions')->change();
        });

        Schema::table('Lecons', function (Blueprint $table) {
            $table->string('Titre')->change();
            $table->text('Descriptions')->change();
        });

        Schema::table('Etapes', function (Blueprint $table) {
            $table->string('Titre')->change();
            $table->text('Descriptions')->change();
        });
    }
};
