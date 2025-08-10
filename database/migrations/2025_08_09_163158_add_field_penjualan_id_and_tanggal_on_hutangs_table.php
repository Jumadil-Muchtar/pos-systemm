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
        Schema::table('hutangs', function (Blueprint $table){
            $table->foreignId('penjualan_id')->nullable()->constrained('penjualans')->cascadeOnDelete();
            $table->dateTime('tanggal')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hutangs', function (Blueprint $table){
            $table->dropForeign(['penjualan_id']);
            $table->dropColumn('penjualan_id');
            $table->dropColumn('tanggal');
        });
    }
};
