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
        Schema::table('user_reussites', function (Blueprint $table) {
            $table->string('context_type')->nullable()->after('reussite_id');
            $table->unsignedBigInteger('context_id')->nullable()->after('context_type');
            
            try {
                $table->dropUnique(['user_id', 'reussite_id']);
            } catch (\Exception $e) {
                // Ignore if it doesn't exist
            }
            
            $table->unique(['user_id', 'reussite_id', 'context_type', 'context_id'], 'user_reussite_context_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_reussites', function (Blueprint $table) {
            $table->dropUnique('user_reussite_context_unique');
            $table->unique(['user_id', 'reussite_id'], 'user_reussite_unique');
            $table->dropColumn(['context_type', 'context_id']);
        });
    }
};
