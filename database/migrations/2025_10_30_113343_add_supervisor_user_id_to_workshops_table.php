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
        Schema::table('workshops', function (Blueprint $table) {
            // إضافة حقل supervisor_user_id كمفتاح أجنبي
            $table->foreignId('supervisor_user_id')->nullable()->constrained('users')->onDelete('set null')->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->dropForeign(['supervisor_user_id']);
            $table->dropColumn('supervisor_user_id');
        });
    }
};