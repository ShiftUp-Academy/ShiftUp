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
        Schema::table('Utilisateurs', function (Blueprint $table) {
            $table->timestamp('DerniereConnexion')->nullable()->after('Role');
            $table->boolean('Newsletter')->default(false)->after('DerniereConnexion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Utilisateurs', function (Blueprint $table) {
            $table->dropColumn(['DerniereConnexion', 'Newsletter']);
        });
    }
};
