<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tiered, per-guest pricing.
 *
 * A tour is no longer one flat rate. Each tour carries its own pricing tiers
 * (single / couple / family / group), and because a couple, family or group may
 * need more than one room or cabin, each tier keeps its own cabin inventory.
 *
 * Four independent levers live on a tier:
 *
 *   1. `price_per_adult` - the adult rate the tier charges.
 *   2. child policy      - ages below `infant_age_max` travel free, ages below
 *                          `child_age_max` pay `child_price_percent` of the
 *                          adult rate.
 *   3. cabin surcharge   - cabins beyond `included_cabin_count` each cost
 *                          `extra_cabin_fee`.
 *   4. tier discount     - `discount_type` / `discount_value`, applied to the
 *                          guest subtotal only, never to the cabin surcharge.
 *
 * Bookings snapshot the tier and each child line, so a later price change never
 * rewrites what a customer was quoted and charged.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tour_pricing_tiers')) {
            return;
        }

        Schema::create('tour_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['single', 'couple', 'family', 'group'])->default('single');
            $table->string('label', 60)->nullable();

            // Adult rate for this tier. Children and infants derive from it.
            $table->decimal('price_per_adult', 10, 2)->default(0);

            // How many paying adults this tier covers.
            $table->unsignedSmallInteger('min_adults')->default(1);
            $table->unsignedSmallInteger('max_adults')->default(1);

            // Age policy. A guest strictly below `infant_age_max` is free, a
            // guest below `child_age_max` pays the child percentage, and
            // everyone else pays the full adult rate.
            $table->unsignedTinyInteger('infant_age_max')->default(3);
            $table->unsignedTinyInteger('child_age_max')->default(8);
            $table->decimal('child_price_percent', 5, 2)->default(50);

            // Cabins. `capacity_per_cabin` decides how many guests fit in one,
            // so extra cabins are derived from the actual headcount.
            $table->unsignedSmallInteger('capacity_per_cabin')->default(4);
            $table->unsignedSmallInteger('included_cabin_count')->default(1);
            $table->decimal('extra_cabin_fee', 10, 2)->default(0);

            // Operator discount for this tier. Applied to the guest subtotal
            // only, so a discount can never erode the cabin surcharge.
            $table->enum('discount_type', ['none', 'percent', 'fixed'])->default('none');
            $table->decimal('discount_value', 10, 2)->default(0);

            // Inventory. `cabins_total` null means the operator is not tracking
            // cabins for this tier.
            $table->unsignedSmallInteger('cabins_total')->nullable();
            $table->unsignedSmallInteger('cabins_booked')->default(0);

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tour_id', 'type']);
            $table->index(['tour_id', 'is_active']);
        });

        // Children are stored one row each because their ages differ, and the
        // age is what decides the rate. Adults are only counted.
        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('age')->nullable();
            $table->enum('type', ['child', 'infant'])->default('child');

            // Snapshot of the money, so history survives a price change.
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('line_total', 10, 2)->default(0);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['booking_id', 'sort_order']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('pricing_tier_id')
                ->nullable()
                ->after('guest_count')
                ->constrained('tour_pricing_tiers')
                ->nullOnDelete();

            $table->string('pricing_tier_type', 20)->nullable()->after('pricing_tier_id');
            $table->string('pricing_tier_label', 60)->nullable()->after('pricing_tier_type');
            $table->decimal('adult_rate', 10, 2)->nullable()->after('pricing_tier_label');

            // Headcount split. `guest_count` stays as the total of the three.
            $table->unsignedSmallInteger('adult_count')->default(1)->after('adult_rate');
            $table->unsignedSmallInteger('child_count')->default(0)->after('adult_count');
            $table->unsignedSmallInteger('infant_count')->default(0)->after('child_count');

            // Cabin money, kept apart from the guest subtotal so the discount
            // base stays honest.
            $table->unsignedSmallInteger('cabin_count')->default(0)->after('infant_count');
            $table->decimal('extra_cabin_amount', 10, 2)->default(0)->after('cabin_count');
            $table->decimal('tier_discount_amount', 10, 2)->default(0)->after('extra_cabin_amount');

            $table->index(['tour_id', 'pricing_tier_type']);
        });

        $this->backfillTiersFromLegacyPrice();
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['pricing_tier_id']);
            $table->dropIndex(['tour_id', 'pricing_tier_type']);
            $table->dropColumn([
                'pricing_tier_id',
                'pricing_tier_type',
                'pricing_tier_label',
                'adult_rate',
                'adult_count',
                'child_count',
                'infant_count',
                'cabin_count',
                'extra_cabin_amount',
                'tier_discount_amount',
            ]);
        });

        Schema::dropIfExists('booking_guests');
        Schema::dropIfExists('tour_pricing_tiers');
    }

    /**
     * Give every existing tour a Single tier at its current per-person rate so
     * no tour is left unpriced. Couple, Family and Group stay absent on
     * purpose: deriving a family price from an adult rate would be a guess, and
     * an operator is better placed to set it than a migration is.
     */
    private function backfillTiersFromLegacyPrice(): void
    {
        $now = now();

        DB::table('tours')->orderBy('id')->each(function (object $tour) use ($now) {
            DB::table('tour_pricing_tiers')->insert([
                'tour_id' => $tour->id,
                'type' => 'single',
                'label' => null,
                'price_per_adult' => (float) $tour->price_per_person,
                'min_adults' => 1,
                'max_adults' => 1,
                'infant_age_max' => 3,
                'child_age_max' => 8,
                'child_price_percent' => 50,
                'capacity_per_cabin' => max(2, (int) ($tour->max_slots ?? 4)),
                'included_cabin_count' => 1,
                'extra_cabin_fee' => 0,
                'discount_type' => 'none',
                'discount_value' => 0,
                'cabins_total' => null,
                'cabins_booked' => 0,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }
};
