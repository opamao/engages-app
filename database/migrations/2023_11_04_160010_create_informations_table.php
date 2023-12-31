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
        Schema::create('informations', function (Blueprint $table) {
            $table->uuid('id_info')->primary();
            $table->string('prenom_garcon');
            $table->string('prenom_fille')->nullable();
            $table->text('message')->nullable();
            $table->string('date_mariage', 30);
            $table->string('couleur', 50)->nullable();
            $table->string('code_mariage', 25)->unique();
            $table->uuid('client_id');
            $table->foreign('client_id')->references('id_client')->on('clients')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informations');
        Schema::table('informations', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
