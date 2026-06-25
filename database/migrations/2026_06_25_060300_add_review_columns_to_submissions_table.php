<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Mengubah tipe kolom status dari enum ke string agar mendukung status Draft
            $table->string('status', 50)->default('Draft')->change();

            // Kolom baru untuk sistem Review Pra-OJS
            $table->string('review_status', 50)->default('pending');
            $table->text('structure_review')->nullable();
            $table->text('abstract_review')->nullable();
            $table->text('introduction_review')->nullable();
            $table->text('method_review')->nullable();
            $table->text('results_review')->nullable();
            $table->text('conclusion_review')->nullable();
            $table->text('bibliography_review')->nullable();
            $table->text('general_suggestions')->nullable();
            $table->text('review_error_message')->nullable();
            $table->timestamp('review_email_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn([
                'review_status',
                'structure_review',
                'abstract_review',
                'introduction_review',
                'method_review',
                'results_review',
                'conclusion_review',
                'bibliography_review',
                'general_suggestions',
                'review_error_message',
                'review_email_sent_at',
            ]);
        });
    }
};
