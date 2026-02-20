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
        Schema::create('NewsletterSubscriptions', function (Blueprint $table) {
            $table->id('IdSubscription');
            $table->string('Email')->unique();
            $table->timestamp('DateSouscription')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('NewsletterSubscriptions');
    }
};
