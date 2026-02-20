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
            // Change Role to string to allow 'moderateur' easily, or we could update enum.
            // But string is more future-proof.
            $table->string('Role')->default('utilisateur')->change();
            $table->json('PermissionsModerateur')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Utilisateurs', function (Blueprint $table) {
            $table->enum('Role', ['utilisateur', 'admin'])->default('utilisateur')->change();
            $table->dropColumn('PermissionsModerateur');
        });
    }
};
