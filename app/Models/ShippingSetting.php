<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    use HasFactory;
    protected $table = "shipping_settings";

    // Mass-assignable origin-address fields. Deliberately excludes 'id' - the
    // singleton row (id = 1) is looked up via ShippingSetting::current() and
    // assigned to directly in ShippingSettingController::edit(), never through
    // mass assignment, so the primary key can never be stripped/overwritten.
    protected $fillable = [
        'origin_company_name',
        'origin_street_address',
        'origin_city',
        'origin_region',
        'origin_postcode',
        'origin_country',
    ];

    // Singleton accessor - the app only ever uses row id = 1 (see the
    // shipping_settings migration). Always returns an object, even before any
    // settings row has been saved, so callers never need to null-check.
    public static function current(): self
    {
        return self::find(1) ?? new self();
    }
}
