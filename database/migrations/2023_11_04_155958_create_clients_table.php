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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id_client')->primary();
            $table->string('nom_client');
            $table->string('prenom_client');
            $table->string('telephone_client', 50)->unique();
            $table->string('email_client')->unique()->nullable();
            $table->string('photo_client')->nullable();
            $table->string('password_client');
            $table->string('otp_client', 4)->nullable();
            $table->string('status_client', 10)->comment('active, desactive')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
