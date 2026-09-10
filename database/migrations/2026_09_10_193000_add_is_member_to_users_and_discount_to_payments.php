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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_member')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_member')->default(false)->after('email');
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'original_amount')) {
                    $table->decimal('original_amount', 12, 2)->nullable()->after('gross_amount');
                }
                if (!Schema::hasColumn('payments', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0)->after('original_amount');
                }
            });
        }

        if (Schema::hasTable('payment_items')) {
            Schema::table('payment_items', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_items', 'original_amount')) {
                    $table->decimal('original_amount', 12, 2)->nullable()->after('gross_amount');
                }
                if (!Schema::hasColumn('payment_items', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0)->after('original_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_member')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_member');
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('payments', 'original_amount')) {
                    $columns[] = 'original_amount';
                }
                if (Schema::hasColumn('payments', 'discount_amount')) {
                    $columns[] = 'discount_amount';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('payment_items')) {
            Schema::table('payment_items', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('payment_items', 'original_amount')) {
                    $columns[] = 'original_amount';
                }
                if (Schema::hasColumn('payment_items', 'discount_amount')) {
                    $columns[] = 'discount_amount';
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
