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
        Schema::create('Themes', function (Blueprint $table) {
            $table->id('IdTheme');
            $table->foreignId('IdProgramme')->constrained('ProgrammeFormation', 'IdProgrammeFormation')->onDelete('cascade');
            $table->string('Titre');
            $table->text('Descriptions')->nullable();
            $table->integer('Ordre')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('Lecons', function (Blueprint $table) {
            $table->foreignId('IdTheme')->nullable()->constrained('Themes', 'IdTheme')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('Lecons', function (Blueprint $table) {
            $table->dropForeign(['IdTheme']);
            $table->dropColumn('IdTheme');
        });
        Schema::dropIfExists('Themes');
    }
};
