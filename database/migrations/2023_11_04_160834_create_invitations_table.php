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
        Schema::create('invitations', function (Blueprint $table) {
            $table->uuid('id_inv')->primary();
            $table->uuid('client_inv');
            $table->string('type_inv', 50)->comment('mariage, anniversaire, bapteme, naissance, etc.');
            $table->string('etat_inv', 20)->comment('accepte, attente', 'refuse')->default('attente');
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
        Schema::dropIfExists('invitations');
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['info_id']);
            $table->dropColumn('info_id');
        });
    }
};
