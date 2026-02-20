<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reussites', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('video_link');
            $table->enum('type_action', [
                'lecon_terminee', 
                'etape_passee', 
                'seuil_points', 
                'reservation_evenement', 
                'temoignage_laisse', 
                'offre_achetee',
                'autre'
            ]);
            $table->json('valeur_requise')->nullable();
            $table->integer('seuil_points')->nullable();
            $table->integer('points_recompense')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });

        Schema::create('user_reussites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('IdUtilisateur')->on('Utilisateurs')->onDelete('cascade');
            $table->foreignId('reussite_id')->constrained()->onDelete('cascade');
            $table->timestamp('date_obtention')->useCurrent();
            $table->timestamps();
            
            $table->unique(['user_id', 'reussite_id']);
        });

        Schema::create('user_reussite_progressions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('IdUtilisateur')->on('Utilisateurs')->onDelete('cascade');
            $table->foreignId('reussite_id')->constrained()->onDelete('cascade');
            $table->integer('progression_actuelle')->default(0);
            $table->integer('progression_requise');
            $table->timestamps();
            
            $table->unique(['user_id', 'reussite_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_reussite_progressions');
        Schema::dropIfExists('user_reussites');
        Schema::dropIfExists('reussites');
    }
};
