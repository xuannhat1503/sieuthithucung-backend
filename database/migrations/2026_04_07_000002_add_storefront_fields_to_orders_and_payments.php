<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('product_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('products')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'payment_method')) {
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash', 'paypal', 'momo') NOT NULL DEFAULT 'cash'");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_id');
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'payment_method')) {
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash', 'paypal') NOT NULL DEFAULT 'cash'");
            }
        }
    }
};
