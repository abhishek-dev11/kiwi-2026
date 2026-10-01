<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $table = "coupons";

    // Single source of truth for "can this coupon be used against this subtotal
    // right now" - used identically by the apply-coupon preview and the final
    // order save so a coupon can never be honoured at checkout after it expired,
    // hit its usage limit, or fell below its minimum order amount.
    public function isRedeemable(float $subtotal): array
    {
        if ($this->status != 'Active' || $this->is_delete) {
            return [false, 'This coupon is no longer active.'];
        }

        $today = now()->toDateString();
        if ($this->start_date && $today < $this->start_date) {
            return [false, 'This coupon is not active yet.'];
        }
        if ($this->end_date && $today > $this->end_date) {
            return [false, 'This coupon has expired.'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return [false, 'This coupon has reached its usage limit.'];
        }

        if ($this->min_order_amount !== null && $subtotal < $this->min_order_amount) {
            return [false, 'This coupon requires a minimum order of $'.number_format($this->min_order_amount, 2).'.'];
        }

        return [true, ''];
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type == 'percentage') {
            $discount = $subtotal * ($this->value / 100);
            if ($this->max_discount_amount !== null) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }
        } else {
            $discount = (float) $this->value;
        }

        // Never let a coupon discount more than the order is actually worth.
        return round(min($discount, $subtotal), 2);
    }

    // Re-validates the session-applied coupon against the current subtotal every
    // time it's needed (cart page, checkout page, order save) rather than trusting
    // a stored discount amount - shared by the Cart and Checkout controllers so a
    // coupon applied on either page behaves identically and stays valid across both.
    public static function resolveFromSession(float $subtotal): array
    {
        $applied = session('applied_coupon');
        if (!$applied) {
            return [null, 0];
        }

        $coupon = self::find($applied['id']);
        if (!$coupon) {
            session()->forget('applied_coupon');
            return [null, 0];
        }

        [$ok, ] = $coupon->isRedeemable($subtotal);
        if (!$ok) {
            session()->forget('applied_coupon');
            return [null, 0];
        }

        return [$coupon, $coupon->calculateDiscount($subtotal)];
    }

    // Shared "apply this code" logic for the Cart and Checkout apply-coupon
    // endpoints - looks up the code, validates it, and stashes it in session on
    // success so it carries over between the cart and checkout pages.
    public static function attemptApply(string $code, float $subtotal): array
    {
        if ($subtotal <= 0) {
            return ['success' => false, 'message' => 'Your cart is empty.'];
        }

        $coupon = self::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
            ->whereNull('is_delete')
            ->first();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid coupon code.'];
        }

        [$ok, $message] = $coupon->isRedeemable($subtotal);
        if (!$ok) {
            return ['success' => false, 'message' => $message];
        }

        $discount = $coupon->calculateDiscount($subtotal);
        session(['applied_coupon' => ['id' => $coupon->id, 'code' => $coupon->code]]);

        return [
            'success'  => true,
            'message'  => 'Coupon applied successfully.',
            'code'     => $coupon->code,
            'discount' => $discount,
        ];
    }
}
