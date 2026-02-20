<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the existing check constraint on 'Role'
        DB::statement('ALTER TABLE "Utilisateurs" DROP CONSTRAINT IF EXISTS "Utilisateurs_Role_check"');
        
        // Add a new check constraint that includes 'moderateur'
        DB::statement('ALTER TABLE "Utilisateurs" ADD CONSTRAINT "Utilisateurs_Role_check" CHECK ("Role"::text = ANY (ARRAY[\'utilisateur\'::character varying, \'admin\'::character varying, \'moderateur\'::character varying]::text[]))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse back to the original constraint without 'moderateur'
        DB::statement('ALTER TABLE "Utilisateurs" DROP CONSTRAINT IF EXISTS "Utilisateurs_Role_check"');
        
        DB::statement('ALTER TABLE "Utilisateurs" ADD CONSTRAINT "Utilisateurs_Role_check" CHECK ("Role"::text = ANY (ARRAY[\'utilisateur\'::character varying, \'admin\'::character varying]::text[]))');
    }
};
