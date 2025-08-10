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
        Schema::table('pelanggans', function (Blueprint $table){
            $table->bigInteger('saldo')->default(0)->change();
            $table->bigInteger('utang')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table){
            $table->dropColumn('saldo');
            $table->dropColumn('utang');
            $table->bigInteger('saldo');
            $table->bigInteger('utang');
        });
        
    }
};
