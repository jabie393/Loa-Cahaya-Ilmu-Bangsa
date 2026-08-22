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
            $table->boolean('want_doi')->default(false);
            $table->boolean('has_doi')->nullable()->default(null);
            $table->string('repository_identifier')->nullable()->unique();
            $table->string('repository_landing_page')->nullable();
            $table->string('repository_redirect_url')->nullable();
            $table->string('repository_identifier_status')->nullable();
            $table->timestamp('repository_identifier_generated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn([
                'want_doi',
                'has_doi',
                'repository_identifier',
                'repository_landing_page',
                'repository_redirect_url',
                'repository_identifier_status',
                'repository_identifier_generated_at',
            ]);
        });
    }
};
