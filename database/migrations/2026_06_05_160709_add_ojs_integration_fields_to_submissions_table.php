<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {

            // Metadata artikel
            $table->longText('abstract')->nullable()->after('title');
            $table->text('keywords')->nullable()->after('abstract');
            $table->string('manuscript_file')->nullable()->after('proof_of_payment');

            // Tracking OJS
            $table->string('ojs_submission_id', 50)->nullable()->after('publication_link');
            $table->string('ojs_status', 50)->nullable()->after('ojs_submission_id');
            $table->timestamp('ojs_synced_at')->nullable()->after('ojs_status');
            $table->text('ojs_error_message')->nullable()->after('ojs_synced_at');

        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {

            $table->dropColumn([
                'abstract',
                'keywords',
                'manuscript_file',
                'ojs_submission_id',
                'ojs_status',
                'ojs_synced_at',
                'ojs_error_message',
            ]);

        });
    }
};