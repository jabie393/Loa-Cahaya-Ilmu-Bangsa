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
        Schema::create('chatbot_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_token')->nullable()->index();
            $table->string('role')->default('user');
            $table->text('message');
            $table->string('session_id')->nullable()->index();
            // Optional: $table->foreign('session_id')->references('id')->on('chatbot_sessions')->nullOnDelete();
            // Since session_id can be created on the fly by frontend before backend creates session, 
            // a strict foreign key might fail if we don't sync properly. Let's just index it.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_histories');
    }
};
