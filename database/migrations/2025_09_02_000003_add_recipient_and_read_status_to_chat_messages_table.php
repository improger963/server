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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('message');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('cascade')->after('message');
            $table->index(['user_id', 'recipient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'recipient_id']);
            $table->dropForeign(['recipient_id']);
            $table->dropColumn(['recipient_id', 'is_read']);
        });
    }
};