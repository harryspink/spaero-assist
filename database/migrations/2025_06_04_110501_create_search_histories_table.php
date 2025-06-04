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
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('search_term');
            $table->integer('results_count')->default(0);
            $table->boolean('success')->default(true);
            $table->json('search_results')->nullable();
            $table->timestamps();
            
            // Add indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['team_id', 'created_at']);
            $table->index('search_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
