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
        Schema::create('project_investor_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('investor_user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('investment_amount', 15, 2)->nullable();
            $table->timestamps();

            // لضمان عدم تكرار المستثمر في نفس المشروع
            $table->unique(['project_id', 'investor_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_investor_links');
    }
};