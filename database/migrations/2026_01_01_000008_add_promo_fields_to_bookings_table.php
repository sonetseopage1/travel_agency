<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->after('guest_count');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
            $table->string('promo_code')->nullable()->after('discount_amount');
            $table->foreignId('promo_code_id')->nullable()->after('promo_code')
                ->constrained('promo_codes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn(['subtotal', 'discount_amount', 'promo_code']);
        });
    }
};
