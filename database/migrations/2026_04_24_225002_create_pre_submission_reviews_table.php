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
        Schema::create('pre_submission_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('author_name');
            $table->string('email');
            $table->foreignId('journal_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title')->nullable();
            $table->string('file_path');
            
            // Detailed Review Sections
            $table->text('structure_review')->nullable();
            $table->text('abstract_review')->nullable();
            $table->text('introduction_review')->nullable();
            $table->text('method_review')->nullable();
            $table->text('results_review')->nullable();
            $table->text('conclusion_review')->nullable();
            $table->text('bibliography_review')->nullable();
            $table->text('general_suggestions')->nullable();
            
            $table->string('status')->default('pending'); // pending, processing, reviewed, failed
            $table->timestamp('email_sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_submission_reviews');
    }
};
