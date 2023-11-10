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
        Schema::create('besoins', function (Blueprint $table) {
            $table->uuid('id_beso')->primary();
            $table->string('photo_beso')->nullable();
            $table->string('libelle_beso');
            $table->integer('prix_beso')->nullable()->default(0);
            $table->string('type_beso', 20)->comment('offrir, libre');
            $table->string('statut_beso', 20)->comment('valide, attente')->default('attente');
            $table->ulid('client_id');
            $table->foreign('client_id')->references('id_client')->on('clients')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('besoins');
        Schema::table('besoins', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
