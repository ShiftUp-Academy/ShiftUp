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
        Schema::table('Offres', function (Blueprint $table) {
            $table->json('Titre')->change();
            $table->json('Descriptions')->change();
        });

        Schema::table('Temoignage', function (Blueprint $table) {
            $table->json('ContenuTexte')->change();
        });

        Schema::table('Categories', function (Blueprint $table) {
            $table->json('Nom')->change();
            $table->json('Descriptions')->change();
        });

        Schema::table('QuestionsLibres', function (Blueprint $table) {
            $table->json('Titre')->change();
            $table->json('ContenuQuestion')->change();
            $table->json('ContenuReponse')->change();
        });

        Schema::table('Consultations', function (Blueprint $table) {
            $table->json('Titre')->change();
            $table->json('Question')->change();
        });

        Schema::table('Lives', function (Blueprint $table) {
            $table->json('Titre')->change();
            $table->json('Descriptions')->change();
        });

        Schema::table('ReponseConsultations', function (Blueprint $table) {
            $table->json('Titre')->change();
            $table->json('Descriptions')->change();
        });

        Schema::table('reussites', function (Blueprint $table) {
            $table->json('nom')->change();
            $table->json('description')->change();
        });

        Schema::table('QuestionsEtapes', function (Blueprint $table) {
            $table->json('Intitule')->change();
        });

        Schema::table('OptionsQuestions', function (Blueprint $table) {
            $table->json('TexteOption')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Offres', function (Blueprint $table) {
            $table->string('Titre', 155)->change();
            $table->text('Descriptions')->change();
        });

        Schema::table('Temoignage', function (Blueprint $table) {
            $table->text('ContenuTexte')->change();
        });

        Schema::table('Categories', function (Blueprint $table) {
            $table->string('Nom', 255)->change();
            $table->text('Descriptions')->change();
        });

        Schema::table('QuestionsLibres', function (Blueprint $table) {
            $table->string('Titre', 255)->change();
            $table->text('ContenuQuestion')->change();
            $table->text('ContenuReponse')->change();
        });

        Schema::table('Consultations', function (Blueprint $table) {
            $table->string('Titre', 255)->change();
            $table->text('Question')->change();
        });

        Schema::table('Lives', function (Blueprint $table) {
            $table->string('Titre', 255)->change();
            $table->text('Descriptions')->change();
        });

        Schema::table('ReponseConsultations', function (Blueprint $table) {
            $table->string('Titre', 255)->change();
            $table->text('Descriptions')->change();
        });

        Schema::table('reussites', function (Blueprint $table) {
            $table->string('nom')->change();
            $table->text('description')->change();
        });

        Schema::table('QuestionsEtapes', function (Blueprint $table) {
            $table->text('Intitule')->change();
        });

        Schema::table('OptionsQuestions', function (Blueprint $table) {
            $table->text('TexteOption')->change();
        });
    }
};
