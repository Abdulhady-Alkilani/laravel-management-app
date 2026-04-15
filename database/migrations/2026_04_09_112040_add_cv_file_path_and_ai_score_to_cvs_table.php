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
        Schema::table('cvs', function (Blueprint $table) {
            $table->string('cv_file_path')->nullable()->after('education'); // مسار ملف السيرة الذاتية (PDF أو صورة)
            $table->integer('ai_score')->nullable()->after('cv_file_path'); // تقييم الذكاء الاصطناعي (0-100)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cvs', function (Blueprint $table) {
            $table->dropColumn(['cv_file_path', 'ai_score']);
        });
    }
};
