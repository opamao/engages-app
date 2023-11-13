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
        Schema::create('personne_contact', function (Blueprint $table) {
            $table->uuid('id_cont')->primary();
            $table->string('nom_cont');
            $table->string('tel_cont', 50);
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
        Schema::dropIfExists('personne_contact');
        Schema::table('personne_contact', function (Blueprint $table) {
            $table->dropForeign(['info_id']);
            $table->dropColumn('info_id');
        });
    }
};
