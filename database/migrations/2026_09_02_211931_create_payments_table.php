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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->json('submission_ids')->nullable();
            $table->string('invoice_number')->nullable()->unique();
            $table->string('order_id')->unique();
            $table->string('transaction_id')->nullable()->index();
            $table->string('payment_method')->default('qris');
            $table->string('type')->default('submission')->index(); // 'submission', 'doi_addon', 'bulk_submission'
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('journal_share', 12, 2)->default(0);
            $table->decimal('developer_gross_share', 12, 2)->default(0);
            $table->decimal('mdr_amount', 12, 2)->default(0);
            $table->decimal('developer_net_share', 12, 2)->default(0);
            $table->string('transaction_status')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed'])->default('pending')->index();
            $table->text('qris_url')->nullable();
            $table->text('qr_string')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'payment_status']);
            $table->index(['type', 'payment_status']);
        });

        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->cascadeOnDelete();
            $table->string('item_type')->default('publication'); // 'publication', 'doi_addon'
            $table->string('item_name')->nullable();
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
        Schema::dropIfExists('payments');
    }
};
