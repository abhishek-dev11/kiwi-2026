<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;
    protected $table = "shipping_methods";

    public function weightRates()
    {
        return $this->hasMany(ShippingWeightRate::class, 'shipping_method_id')->orderBy('min_weight', 'asc');
    }

    // Mirrors the Active/not-deleted filtering used by Coupon and Supplier
    // lookups elsewhere in the app (is_delete has no default and is left NULL
    // until soft-deleted, so "not deleted" is whereNull, never = 0).
    public static function active()
    {
        return self::where('status', 'Active')->whereNull('is_delete');
    }

    // Single source of truth for "what does this method cost for a given total
    // order weight" - used by checkout so the flat vs weight-based logic only
    // lives in one place.
    public function calculateCost(float $totalWeightKg): ?float
    {
        if ($this->type === 'flat') {
            return $this->flat_rate !== null ? (float) $this->flat_rate : null;
        }

        if ($this->type === 'weight_based') {
            $rates = $this->weightRates()->get();

            if ($rates->isEmpty()) {
                // No brackets configured at all - caller must treat this as
                // "not usable right now", never silently charge $0.
                return null;
            }

            foreach ($rates as $rate) {
                $withinMin = (float) $rate->min_weight <= $totalWeightKg;
                $withinMax = $rate->max_weight === null || (float) $rate->max_weight >= $totalWeightKg;

                if ($withinMin && $withinMax) {
                    return (float) $rate->rate;
                }
            }

            // No bracket matched. That's only "weight exceeds every bracket's
            // max_weight" - warranting a best-effort ceiling fallback to the
            // highest bracket's rate - when totalWeightKg is actually above that
            // bracket's max_weight. A weight below the lowest bracket's min_weight,
            // or one that falls into a gap between brackets, is a configuration
            // gap rather than an over-the-top parcel, so treat it the same as "no
            // brackets configured": return null rather than silently charging the
            // heaviest tier's rate.
            $highest = $rates->sortByDesc('min_weight')->first();
            if ($highest->max_weight !== null && (float) $highest->max_weight < $totalWeightKg) {
                return (float) $highest->rate;
            }

            return null;
        }

        return null;
    }
}
