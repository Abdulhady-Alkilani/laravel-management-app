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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade'); // أضفنا ->nullable() هنا
            $table->foreignId('workshop_id')->nullable()->constrained('workshops')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null'); // إذا كان التقرير يتعلق بخدمة عامة
            $table->string('report_type'); // "Progress Report", "Cost Report", "Productivity Report", "Service Report"
            $table->text('report_details')->nullable(); // يمكن أن يكون نصاً أو JSON لبيانات هيكلية
            $table->string('report_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};