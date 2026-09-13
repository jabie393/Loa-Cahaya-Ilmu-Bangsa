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
        Schema::create('submission_pricings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('category')->index(); // 'issn', 'international', 'addon', 'setting'
            $table->string('tier_name');
            $table->unsignedInteger('min_authors')->nullable();
            $table->unsignedInteger('max_authors')->nullable();
            $table->boolean('with_doi')->nullable();
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('developer_gross_share', 12, 2)->default(0);
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_pricings');
    }
};
