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
            $table->integer('stok');
            $table->integer('jumlah');
            $table->integer('dipajang');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table){
            $table->dropColumn('stok');
            $table->dropColumn('jumlah');
            $table->dropColumn('dipajang');
            $table->dropSoftDeletes();
        });
    }
};
