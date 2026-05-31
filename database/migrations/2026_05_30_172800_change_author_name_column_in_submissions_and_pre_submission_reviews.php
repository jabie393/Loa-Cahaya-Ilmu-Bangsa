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
        Schema::table('submissions', function (Blueprint $table) {
            $table->text('author_name')->change();
        });

        Schema::table('pre_submission_reviews', function (Blueprint $table) {
            $table->text('author_name')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('author_name', 255)->change();
        });

        Schema::table('pre_submission_reviews', function (Blueprint $table) {
            $table->string('author_name', 255)->change();
        });
    }
};
