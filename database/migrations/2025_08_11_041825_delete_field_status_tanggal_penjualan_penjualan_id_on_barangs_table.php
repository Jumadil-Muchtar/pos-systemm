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
        Schema::table('barangs', function (Blueprint $table){
            $table->dropForeign(['penjualan_id']);
            $table->dropColumn('penjualan_id');
            $table->dropColumn('status');
            $table->dropColumn('tanggal_penjualan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table->foreignId('penjualan_id')->constrained('penjualans')->cascadeOnDelete();
        $table->date('tanggal_penjualan')->nullable();
        $table->string('status');
    }
};
