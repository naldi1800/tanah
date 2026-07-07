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
        Schema::create('tanahs', function (Blueprint $table) {
            $table->id();
            $table->string('Alamat');
            $table->foreignId('jenis_tanah_id')->constrained('jenis_tanahs')->onDelete('cascade');
            $table->float('PH_Tanah');
            $table->float('Kelembaban_Tanah');
            $table->float('Suhu_Tanah');
            $table->float('Ketinggian_Tanah');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanahs');
    }
};
