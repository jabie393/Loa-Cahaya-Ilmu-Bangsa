<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plagiarism_paraphrases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plagiarism_check_id')->constrained('plagiarism_checks')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->decimal('original_score', 5, 2);
            $table->decimal('estimated_new_score', 5, 2)->nullable();
            $table->json('improvements')->nullable(); // format: [{"original": "...", "improved": "...", "explanation": "..."}]
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plagiarism_paraphrases');
    }
};
