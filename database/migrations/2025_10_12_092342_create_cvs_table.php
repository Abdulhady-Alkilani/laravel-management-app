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
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('profile_details')->nullable();
            // $table->text('skills')->nullable(); // قم بإزالة هذا السطر أو جعله كتعليق
            $table->text('experience')->nullable();
            $table->text('education')->nullable();
            $table->string('cv_status')->default('قيد الانتظار'); // "تحتاج تأكيد", "تمت الموافقة", "قيد الانتظار"
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cvs');
    }
};