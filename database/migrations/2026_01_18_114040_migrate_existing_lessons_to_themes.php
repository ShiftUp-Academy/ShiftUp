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
        $programmes = \App\Models\ProgrammeFormation::with('lecons')->get();

        foreach ($programmes as $programme) {
            // Check if program has themes (it shouldn't if we just added the table, unless created recently)
            $theme = \App\Models\Theme::where('IdProgramme', $programme->IdProgrammeFormation)->first();

            if (!$theme) {
                $theme = \App\Models\Theme::create([
                    'IdProgramme' => $programme->IdProgrammeFormation,
                    'Titre' => 'Chapitre 1',
                    'Descriptions' => 'Introduction',
                    'Ordre' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            foreach ($programme->lecons as $lecon) {
                if (is_null($lecon->IdTheme)) {
                    $lecon->IdTheme = $theme->IdTheme;
                    $lecon->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            //
        });
    }
};
