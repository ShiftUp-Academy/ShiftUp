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
        Schema::create('ReponseConsultations', function (Blueprint $table) {
            $table->id('IdReponseConsultation');
            $table->bigInteger('IdCategorie')->nullable();
            $table->string('Titre', 255)->nullable();
            $table->text('Descriptions')->nullable();
            $table->text('LienVideo')->nullable();
            $table->string('Statut', 50)->default('Publié');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent();

            $table->foreign('IdCategorie')->references('IdCategorie')->on('Categories')->onDelete('set null');
        });

        Schema::create('ReponseConsultation_Items', function (Blueprint $table) {
            $table->bigInteger('IdReponseConsultation');
            $table->bigInteger('IdConsultation');
            
            $table->primary(['IdReponseConsultation', 'IdConsultation']);
            $table->foreign('IdReponseConsultation')->references('IdReponseConsultation')->on('ReponseConsultations')->onDelete('cascade');
            $table->foreign('IdConsultation')->references('IdConsultation')->on('Consultations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ReponseConsultation_Items');
        Schema::dropIfExists('ReponseConsultations');
    }
};
