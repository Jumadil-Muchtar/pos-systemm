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
        Schema::table('barang_terjuals', function (Blueprint $table){
            $table->bigInteger('total_harga');
            $table->bigInteger('keuntungan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_terjuals', function (Blueprint $table){
            $table->dropColumn('total_harga');
            $table->dropColumn('keuntungan');
        });
    }
};
