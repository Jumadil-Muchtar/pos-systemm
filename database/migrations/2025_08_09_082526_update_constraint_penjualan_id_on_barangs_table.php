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
            $table->foreignId('penjualan_id')->nullable()->change();
            $table->foreign('penjualan_id')
                ->references('id')
                ->on('penjualans')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table){
            $table->dropForeign(['penjualan_id']);
            $table->foreignId('penjualan_id')->change();
            $table->foreign('penjualan_id')
                ->references('id')
                ->on('penjualans')
                ->cascadeOnDelete();
        });
        
    }
};
