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
        Schema::table('penjualans', function (Blueprint $table){
            $table->dropColumn('tanggal_pembayaran');
            $table->dateTime('tanggal_penjualan');
            $table->dateTime('tanggal_pelunasan')->nullable()->change();
            $table->bigInteger('jumlah_utang')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function(Blueprint $table){
            $table->dateTime('tanggal_pembayaran');
            $table->dropColumn('tanggal_penjualan');
            $table->dateTime('tanggal_pelunasan')->nullable(false)->change();
            $table->dateTime('jumlah_utang')->nullable(false)->change();

        });
    }
};
