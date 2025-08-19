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
            $table->foreignId('penjualan_id')->constrained('penjualans')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_terjuals', function (Blueprint $table){
            $table->dropForeign(['penjualan_id']);
            $table->dropColumn('penjualan_id');
        });
    }
};
