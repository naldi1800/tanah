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
        Schema::table('tanahs', function (Blueprint $table) {
            // Drop the ketinggian_tanah column
            $table->dropColumn('Ketinggian_Tanah');
            
            // Add the drainase column
            $table->string('drainase')->after('Suhu_Tanah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tanahs', function (Blueprint $table) {
            // Drop the drainase column
            $table->dropColumn('drainase');
            
            // Restore the ketinggian_tanah column
            $table->float('Ketinggian_Tanah')->after('Suhu_Tanah');
        });
    }
};
