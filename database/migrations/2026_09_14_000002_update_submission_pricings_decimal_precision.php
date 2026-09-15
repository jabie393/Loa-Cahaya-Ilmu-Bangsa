<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('submission_pricings', function (Blueprint $table) {
            $table->decimal('gross_amount', 14, 4)->default(0)->change();
            $table->decimal('developer_gross_share', 14, 4)->default(0)->change();
        });

        // Correct the MDR rate to 0.0070
        DB::table('submission_pricings')
            ->where('key', 'setting_mdr_rate')
            ->update(['gross_amount' => 0.0070]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_pricings', function (Blueprint $table) {
            $table->decimal('gross_amount', 12, 2)->default(0)->change();
            $table->decimal('developer_gross_share', 12, 2)->default(0)->change();
        });
    }
};
