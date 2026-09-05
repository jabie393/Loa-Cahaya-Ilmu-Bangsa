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
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->cascadeOnDelete();
            $table->string('item_type')->default('publication'); // 'publication', 'doi_addon'
            $table->text('item_name')->nullable();
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
    }
};
