<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->enum('type', ['percent', 'fixed', 'shipping']);
                $table->decimal('discount', 10, 2);
                $table->decimal('min_subtotal', 10, 2)->default(0);
                $table->decimal('max_discount', 10, 2)->nullable();
                $table->string('label', 191)->nullable();
                $table->timestamp('expired_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $now = now();
        $defaults = [
            [
                'code' => 'PET10',
                'type' => 'percent',
                'discount' => 10,
                'min_subtotal' => 200000,
                'max_discount' => 50000,
                'label' => 'Giam 10% toi da 50.000d cho don tu 200.000d',
            ],
            [
                'code' => 'SAVE30K',
                'type' => 'fixed',
                'discount' => 30000,
                'min_subtotal' => 300000,
                'max_discount' => null,
                'label' => 'Giam truc tiep 30.000d cho don tu 300.000d',
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'shipping',
                'discount' => 30000,
                'min_subtotal' => 150000,
                'max_discount' => null,
                'label' => 'Mien phi van chuyen toi da 30.000d',
            ],
            [
                'code' => 'THUANNGU',
                'type' => 'percent',
                'discount' => 100,
                'min_subtotal' => 0,
                'max_discount' => null,
                'label' => 'Noi hay lam giam cho 100% nhe',
            ],
        ];

        foreach ($defaults as $coupon) {
            $existing = DB::table('coupons')->where('code', $coupon['code'])->first();

            if ($existing) {
                DB::table('coupons')
                    ->where('code', $coupon['code'])
                    ->update([
                        'type' => $coupon['type'],
                        'discount' => $coupon['discount'],
                        'min_subtotal' => $coupon['min_subtotal'],
                        'max_discount' => $coupon['max_discount'],
                        'label' => $coupon['label'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);

                continue;
            }

            DB::table('coupons')->insert([
                'code' => $coupon['code'],
                'type' => $coupon['type'],
                'discount' => $coupon['discount'],
                'min_subtotal' => $coupon['min_subtotal'],
                'max_discount' => $coupon['max_discount'],
                'label' => $coupon['label'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
