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
        // 1. Référenciels
        Schema::create('Categories', function (Blueprint $table) {
            $table->id('IdCategorie');
            $table->string('Nom', 100);
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('Tags', function (Blueprint $table) {
            $table->id('IdTag');
            $table->string('Nom', 100);
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        // 2. Coaching & Consultations
        Schema::create('Consultations', function (Blueprint $table) {
            $table->id('IdConsultation');
            $table->foreignId('IdUtilisateur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->string('Titre', 155)->nullable();
            $table->text('Question')->nullable();
            $table->string('Statut', 255)->default('Ouverte'); // CHECK (Statut IN ('Ouverte', 'En cours', 'Fermée'))
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        // 3. Formations
        Schema::create('ProgrammeFormation', function (Blueprint $table) {
            $table->id('IdProgrammeFormation');
            $table->string('Titre', 155);
            $table->text('LienPhoto')->nullable();
            $table->string('Statut', 50)->default('Dépublié');
            $table->string('StatutVerrouillageProgression', 50)->default('Verrouillé');
            $table->text('Descriptions')->nullable();
            $table->decimal('Prix', 10, 2)->nullable();
            $table->integer('PointsOfferts')->default(0);
            $table->foreignId('idAuteur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('Lecons', function (Blueprint $table) {
            $table->id('IdLecon');
            $table->foreignId('IdProgramme')->constrained('ProgrammeFormation', 'IdProgrammeFormation')->onDelete('cascade');
            $table->string('Titre', 155);
            $table->text('LienPhoto')->nullable();
            $table->string('Statut', 50)->default('Dépublié');
            $table->text('Descriptions')->nullable();
            $table->string('TypeLecon', 255);
            $table->string('SourceType', 255);
            $table->text('Contenu');
            $table->integer('DureeVideo')->nullable();
            $table->integer('NombreDePages')->nullable();
            $table->integer('Ordre')->default(0);
            $table->integer('PointsOfferts')->default(0);
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        // 4. Questions Libres & Support
        Schema::create('QuestionsLibres', function (Blueprint $table) {
            $table->id('IdQuestionLibre');
            $table->foreignId('IdUtilisateur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->foreignId('IdProgramme')->nullable()->constrained('ProgrammeFormation', 'IdProgrammeFormation')->onDelete('cascade');
            $table->foreignId('IdLecon')->nullable()->constrained('Lecons', 'IdLecon')->onDelete('cascade');
            $table->foreignId('IdConsultation')->nullable()->constrained('Consultations', 'IdConsultation')->onDelete('cascade');
            $table->string('Titre', 255)->nullable();
            $table->text('ContenuQuestion');
            $table->foreignId('IdCategorie')->nullable()->constrained('Categories', 'IdCategorie')->onDelete('set null');
            $table->string('TypeReponse', 255)->nullable();
            $table->text('ContenuReponse')->nullable();
            $table->string('StatutReponse', 255)->default('En attente');
            $table->timestamp('DateReponse')->nullable();
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('QuestionsTags', function (Blueprint $table) {
            $table->foreignId('IdQuestion')->constrained('QuestionsLibres', 'IdQuestionLibre')->onDelete('cascade');
            $table->foreignId('IdTag')->constrained('Tags', 'IdTag')->onDelete('cascade');
            $table->primary(['IdQuestion', 'IdTag']);
        });

        // 5. Exercices & Étapes
        Schema::create('Etapes', function (Blueprint $table) {
            $table->id('IdEtape');
            $table->foreignId('IdLecon')->constrained('Lecons', 'IdLecon')->onDelete('cascade');
            $table->string('Titre', 155);
            $table->text('Descriptions')->nullable();
            $table->string('Statut', 50)->default('Dépublié');
            $table->string('TypeEtape', 255);
            $table->integer('Ordre')->default(0);
            $table->integer('PointsOfferts')->default(0);
            $table->boolean('NecessiteValidationAdmin')->default(false);
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('QuestionsEtapes', function (Blueprint $table) {
            $table->id('IdQuestion');
            $table->foreignId('IdEtape')->constrained('Etapes', 'IdEtape')->onDelete('cascade');
            $table->text('Intitule');
            $table->string('TypeQuestion', 255);
            $table->integer('Ordre')->default(0);
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('OptionsQuestions', function (Blueprint $table) {
            $table->id('IdOption');
            $table->foreignId('IdQuestion')->constrained('QuestionsEtapes', 'IdQuestion')->onDelete('cascade');
            $table->text('TexteOption');
            $table->boolean('EstCorrecte')->default(false);
            $table->integer('Ordre')->default(0);
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        // 6. Suivi de Progression
        Schema::create('ReponsesEtapesUtilisateur', function (Blueprint $table) {
            $table->id('IdReponse');
            $table->foreignId('IdUtilisateur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->foreignId('IdEtape')->constrained('Etapes', 'IdEtape')->onDelete('cascade');
            $table->string('StatutValidation', 255)->default('En attente');
            $table->text('ReponseAdmin')->nullable();
            $table->timestamp('DateValidation')->nullable();
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['IdUtilisateur', 'IdEtape'], 'Unique_Reponse_Utilisateur');
        });

        Schema::create('DetailsReponsesUtilisateur', function (Blueprint $table) {
            $table->id('IdDetail');
            $table->foreignId('IdReponse')->constrained('ReponsesEtapesUtilisateur', 'IdReponse')->onDelete('cascade');
            $table->foreignId('IdQuestion')->constrained('QuestionsEtapes', 'IdQuestion')->onDelete('cascade');
            $table->text('TexteReponse')->nullable(); 
            $table->foreignId('IdOptionChoisie')->nullable()->constrained('OptionsQuestions', 'IdOption')->onDelete('set null');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('ProgressionUtilisateur', function (Blueprint $table) {
            $table->id('IdProgression');
            $table->foreignId('IdUtilisateur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');  
            $table->foreignId('IdProgramme')->nullable()->constrained('ProgrammeFormation', 'IdProgrammeFormation')->onDelete('cascade');
            $table->foreignId('IdLecon')->nullable()->constrained('Lecons', 'IdLecon')->onDelete('cascade');
            $table->foreignId('IdEtape')->nullable()->constrained('Etapes', 'IdEtape')->onDelete('cascade');  
            $table->boolean('EstTermine')->default(false);
            $table->integer('PointsGagnes')->default(0);
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['IdUtilisateur', 'IdProgramme', 'IdLecon', 'IdEtape'], 'Unique_Progression');
        });

        // 7. Gamification
        Schema::create('Reussite', function (Blueprint $table) {
            $table->id('IdReussite');
            $table->string('Nom', 255);
            $table->text('Descriptions')->nullable();
            $table->text('LienIcone')->nullable();
            $table->string('TypeDeclencheur', 255);
            $table->integer('SeuilPoints')->nullable(); 
            $table->bigInteger('IdReference')->nullable();
            $table->string('Statut', 20)->default('Publié');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('BadgesUtilisateur', function (Blueprint $table) {
            $table->foreignId('IdUtilisateur')->constrained('Utilisateurs', 'IdUtilisateur')->onDelete('cascade');
            $table->foreignId('IdReussite')->constrained('Reussite', 'IdReussite')->onDelete('cascade');
            $table->primary(['IdUtilisateur', 'IdReussite']);
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BadgesUtilisateur');
        Schema::dropIfExists('Reussite');
        Schema::dropIfExists('ProgressionUtilisateur');
        Schema::dropIfExists('DetailsReponsesUtilisateur');
        Schema::dropIfExists('ReponsesEtapesUtilisateur');
        Schema::dropIfExists('OptionsQuestions');
        Schema::dropIfExists('QuestionsEtapes');
        Schema::dropIfExists('Etapes');
        Schema::dropIfExists('QuestionsTags');
        Schema::dropIfExists('QuestionsLibres');
        Schema::dropIfExists('Lecons');
        Schema::dropIfExists('ProgrammeFormation');
        Schema::dropIfExists('Consultations');
        Schema::dropIfExists('Tags');
        Schema::dropIfExists('Categories');
    }
};

