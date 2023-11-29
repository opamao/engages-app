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
        Schema::create('programmes', function (Blueprint $table) {
            $table->uuid('id_prog')->primary();
            $table->string('titre_pro')->nullable();
            $table->string('lieu_pro');
            $table->dateTime('date_pro');
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
        Schema::dropIfExists('programmes');
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropForeign(['info_id']);
            $table->dropColumn('info_id');
        });
    }
};
