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
        Schema::table('ad_slots', function (Blueprint $table) {
            // Rename size column to type
            $table->renameColumn('size', 'type');
            
            // Add dimensions column for banner dimensions
            $table->jsonb('dimensions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_slots', function (Blueprint $table) {
            // Rename type column back to size
            $table->renameColumn('type', 'size');
            
            // Drop dimensions column
            $table->dropColumn('dimensions');
        });
    }
};