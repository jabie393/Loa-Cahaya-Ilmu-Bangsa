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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('submission_id')->nullable()->change();
            $table->json('submission_ids')->nullable()->after('submission_id');
        });

        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('journal_share', 12, 2)->default(0);
            $table->decimal('developer_gross_share', 12, 2)->default(0);
            $table->decimal('mdr_amount', 12, 2)->default(0);
            $table->decimal('developer_net_share', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_items');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('submission_ids');
            $table->foreignId('submission_id')->nullable(false)->change();
        });
    }
};
