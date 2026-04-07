<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shipping_addresses')) {
            return;
        }

        Schema::table('shipping_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_addresses', 'is_order_snapshot')) {
                $table->boolean('is_order_snapshot')->default(false)->after('ward_code');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('shipping_addresses')) {
            return;
        }

        Schema::table('shipping_addresses', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_addresses', 'is_order_snapshot')) {
                $table->dropColumn('is_order_snapshot');
            }
        });
    }
};
