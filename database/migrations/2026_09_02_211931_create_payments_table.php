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
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->string('transaction_id')->nullable()->index();
            $table->string('payment_method')->default('qris');
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('journal_share', 12, 2);
            $table->decimal('developer_gross_share', 12, 2);
            $table->decimal('mdr_amount', 12, 2);
            $table->decimal('developer_net_share', 12, 2);
            $table->string('transaction_status')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'expired', 'failed'])->default('pending')->index();
            $table->text('qris_url')->nullable();
            $table->text('qr_string')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
