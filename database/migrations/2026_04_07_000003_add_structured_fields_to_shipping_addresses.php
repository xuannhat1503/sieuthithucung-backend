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
            if (!Schema::hasColumn('shipping_addresses', 'address_line')) {
                $table->string('address_line')->nullable()->after('address');
            }

            if (!Schema::hasColumn('shipping_addresses', 'province_name')) {
                $table->string('province_name')->nullable()->after('address_line');
            }

            if (!Schema::hasColumn('shipping_addresses', 'province_code')) {
                $table->string('province_code', 20)->nullable()->after('province_name');
            }

            if (!Schema::hasColumn('shipping_addresses', 'district_name')) {
                $table->string('district_name')->nullable()->after('province_code');
            }

            if (!Schema::hasColumn('shipping_addresses', 'district_code')) {
                $table->string('district_code', 20)->nullable()->after('district_name');
            }

            if (!Schema::hasColumn('shipping_addresses', 'ward_name')) {
                $table->string('ward_name')->nullable()->after('district_code');
            }

            if (!Schema::hasColumn('shipping_addresses', 'ward_code')) {
                $table->string('ward_code', 20)->nullable()->after('ward_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('shipping_addresses')) {
            return;
        }

        Schema::table('shipping_addresses', function (Blueprint $table) {
            foreach (['ward_code', 'ward_name', 'district_code', 'district_name', 'province_code', 'province_name', 'address_line'] as $column) {
                if (Schema::hasColumn('shipping_addresses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
