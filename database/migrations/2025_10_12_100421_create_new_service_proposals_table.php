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
        Schema::create('new_service_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('proposed_service_name');
            $table->text('service_details');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // المستخدم الذي اقترح الخدمة
            $table->date('proposal_date');
            $table->string('status')->default('قيد المراجعة'); // "قيد المراجعة", "تمت الموافقة", "مرفوض"
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->onDelete('set null'); // المستخدم الذي راجع الاقتراح
            $table->text('review_comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_service_proposals');
    }
};