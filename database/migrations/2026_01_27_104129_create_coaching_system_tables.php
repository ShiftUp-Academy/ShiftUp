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
        Schema::create('TypeDeCoaching', function (Blueprint $table) {
            $table->bigIncrements('IdTypeCoaching');
            $table->string('NomDeType', 100);
            $table->text('Descriptions')->nullable();
            $table->decimal('Prix', 10, 2)->default(0);
            $table->string('Statut', 50)->default('Dépublié');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent();
        });

        Schema::create('DisponibiliteCoaching', function (Blueprint $table) {
            $table->bigIncrements('IdDisponibilite');
            $table->date('DateDisponible');
            $table->time('HeureDebut');
            $table->time('HeureFin');
            $table->boolean('EstReserve')->default(false);
            $table->timestamp('DateCreation')->useCurrent();
            
            $table->unique(['DateDisponible', 'HeureDebut']);
        });

        Schema::create('ReservationCoaching', function (Blueprint $table) {
            $table->bigIncrements('IdReservation');
            $table->bigInteger('IdUtilisateur');
            $table->bigInteger('IdTypeCoaching')->nullable();
            $table->bigInteger('IdDisponibilite')->nullable();
            $table->string('StatutReservation', 50)->default('En attente');
            $table->text('NoteUtilisateur')->nullable();
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent();

            $table->foreign('IdTypeCoaching')->references('IdTypeCoaching')->on('TypeDeCoaching')->onDelete('set null');
            $table->foreign('IdDisponibilite')->references('IdDisponibilite')->on('DisponibiliteCoaching')->onDelete('set null');
        });

        Schema::create('ReplayCoaching', function (Blueprint $table) {
            $table->bigIncrements('IdReplay');
            $table->bigInteger('IdReservation')->nullable();
            $table->string('Titre', 255)->nullable();
            $table->text('LienVideo')->nullable();
            $table->timestamp('DateUpload')->useCurrent();

            $table->foreign('IdReservation')->references('IdReservation')->on('ReservationCoaching')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ReplayCoaching');
        Schema::dropIfExists('ReservationCoaching');
        Schema::dropIfExists('DisponibiliteCoaching');
        Schema::dropIfExists('TypeDeCoaching');
    }
};
