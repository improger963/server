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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['impression', 'click', 'spend', 'earning']);
            $table->unsignedBigInteger('related_id');
            $table->string('related_type'); // 'campaign', 'site', etc.
            $table->decimal('cost', 15, 4)->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'type']);
            $table->index(['related_id', 'related_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};