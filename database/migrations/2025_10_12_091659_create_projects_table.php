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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location');
            $table->decimal('budget', 15, 2);
            $table->date('start_date');
            $table->date('end_date_planned');
            $table->date('end_date_actual')->nullable();
            $table->string('status')->default('مخطط'); // "مخطط", "قيد التنفيذ", "مكتمل", "متوقف"
            $table->foreignId('manager_user_id')->constrained('users')->onDelete('restrict'); // يجب أن يكون مدير المشروع موجوداً
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};