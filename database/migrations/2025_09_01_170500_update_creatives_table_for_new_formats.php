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
        Schema::table('creatives', function (Blueprint $table) {
            // Update type column to be a string (was already string, but we're just being explicit)
            $table->string('type')->change();
            
            // Change content column to JSONB for better structure support
            // We need to use a raw SQL statement to properly convert text to jsonb in PostgreSQL
            DB::statement('ALTER TABLE creatives ALTER COLUMN content TYPE JSONB USING content::JSONB');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creatives', function (Blueprint $table) {
            // Revert content column to text
            DB::statement('ALTER TABLE creatives ALTER COLUMN content TYPE TEXT');
        });
    }
};