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
        Schema::create('galeries', function (Blueprint $table) {
            $table->uuid('id_gal')->primary();
            $table->string('photo_gal');
            $table->string('libelle_gal')->comment("le prenom du marie et la mariee");
            $table->string('type_gal', 50)->comment('mariage, anniversaire, bapteme, naissance, etc.');
            $table->uuid('info_id');
            $table->foreign('info_id')->references('id_info')->on('informations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galeries');
        Schema::table('galeries', function (Blueprint $table) {
            $table->dropForeign(['info_id']);
            $table->dropColumn('info_id');
        });
    }
};
