<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * il va permettre d'offrir ou de faire une participation libre sur les besoins
     * pour offrir je mets juste l'etat offrir
     * pour participation libre mettre plusieurs lignes sur sa participation
     * Il faut afficher au marié la personne qui fait la participation ou l'offre
     */
    public function up(): void
    {
        Schema::create('offres_besoins', function (Blueprint $table) {
            $table->uuid('id_off_beso')->primary();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id_client')->on('clients');
            $table->string('contact_client_invite', 50);
            $table->integer('montant_libre')->nullable();
            $table->uuid('besoin_id');
            $table->foreign('besoin_id')->references('id_beso')->on('besoins')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offres_besoins');
        Schema::table('offres_besoins', function (Blueprint $table) {
            $table->dropForeign(['client_id', 'id_beso']);
            $table->dropColumn('client_id');
            $table->dropColumn('id_beso');
        });
    }
};
