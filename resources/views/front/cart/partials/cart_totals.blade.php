<div class="cardtotal_top">
    <h3>Cart Totals</h3>

    <div class="coupon_box" style="margin-bottom:15px;">
        <div class="coupon_input_row" id="couponInputRow" style="display:flex; gap:8px; {{ $total_arr['coupon_code'] ? 'display:none;' : '' }}">
            <input type="text" id="coupon_code" class="form-control" placeholder="Enter coupon code" style="text-transform:uppercase;">
            <button type="button" id="applyCouponBtn" class="btn btn-primary">Apply</button>
        </div>
        <div class="coupon_applied_row" id="couponAppliedRow" style="display:{{ $total_arr['coupon_code'] ? 'flex' : 'none' }}; justify-content:space-between; align-items:center;">
            <span>Coupon <strong id="appliedCouponCode">{{ $total_arr['coupon_code'] }}</strong> applied</span>
            <a href="javascript:void(0);" id="removeCouponBtn" style="color:#e74c3c;">Remove</a>
        </div>
        <span id="couponMessage" style="font-size:13px; display:none; margin-top:5px;"></span>
    </div>

    <div class="cardtotal_info">
        <p>Sub total</p>
        <p class="price"><span>$</span>{{ number_format($total_arr['sub_totals'], 2) }}</p>
    </div>
    <div class="cardtotal_info" id="discountRow" style="{{ $total_arr['discount'] > 0 ? '' : 'display:none;' }}">
        <p>Discount<span id="discountCouponLabel">{{ $total_arr['coupon_code'] ? ' ('.$total_arr['coupon_code'].')' : '' }}</span></p>
        <p class="discount">-<span>$</span>{{ number_format($total_arr['discount'], 2) }}</p>
    </div>
</div>
<div class="cardtotal_bottom">
    <div class="total">
        <h6>Total</h6>
        <h6 class="price"><span>$</span>{{ number_format($total_arr['total'], 2) }}</h6>
    </div>
    <div class="checkout-btn">
        <a href="{{ route('checkout') }}">Proceed to checkout</a>
    </div>
</div>