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
            $table->bigInteger('harga_beli');
            $table->bigInteger('harga_jual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function(Blueprint $table){
            $table->dropColumn('harga_beli');
            $table->dropColumn('harga_jual');
        });
    }
};
