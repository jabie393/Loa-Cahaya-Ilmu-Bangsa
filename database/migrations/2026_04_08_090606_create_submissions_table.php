<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();

            // Data utama
            $table->string('author_name');
            $table->string('title');
            $table->string('institution');
            $table->string('email');

            // Journal (dropdown)
            $table->foreignId('journal_id')->nullable();

            // Detail tambahan
            $table->string('volume')->nullable();
            $table->string('publication_link')->nullable();
            $table->date('date_of_loa')->nullable();

            // File (path gambar)
            $table->string('proof_of_payment')->nullable();

            // Status
            $table->enum('status', ['Pending', 'Approved'])->default('Pending');

            // Tanggal
            $table->date('submission_date')->nullable();
            $table->date('approved_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};