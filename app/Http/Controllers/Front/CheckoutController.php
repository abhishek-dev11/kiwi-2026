<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Models\Customers;
use App\Models\CustomerGroups;
use App\Models\CustomerAddress;
use App\Models\Categories;
use App\Models\Products;
use App\Models\ProductGallery;
use App\Models\Attributes;
use App\Models\ProductVariants;
use App\Models\ProductVariantImages;
use App\Models\ProductSubVariants;
use App\Models\ProducSubtVariantItems;
use App\Models\Cart;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderMeta;
use App\Models\Coupon;
use App\Models\ShippingMethod;


class CheckoutController extends Controller
{


    public function checkout_old(Request $request){

        $customer = Auth::guard('customers')->user();

        $get_carts = Cart::with('product')->where('customer_id', $customer->id)->latest()->get();
        $cart_arrs = [];
        $sub_totals = 0;
        $discount = 10;
        $tax = 10;
        $shipping_charges = 15;

        foreach ($get_carts as $value) {
            $product = Products::where('id', $value->product_id)->first();
            $originalPrice = $product->regular_price;
            $salePrice     = $product->discount_price;

            if ($product->product_type == 'variable') {
                $variant_item = ProducSubtVariantItems::where(['product_id' => $value->product_id]);
                if ($value->product_variant_id != '') $variant_item->where(['product_variant_id' => $value->product_variant_id]);
                if ($value->product_sub_variant_id != '') $variant_item->where(['product_sub_variant_id' => $value->product_sub_variant_id]);
                $variant_item = $variant_item->first();
                $finalPrice = $variant_item ? calculateProductPrice($variant_item->price) : 0;

                $get_attribute = Attributes::where(['id' => $product->attribute_id])->first();
                $attribute_title = $get_attribute->title ?? '';
                $get_variant_name = ProductVariants::where(['id' => $value->product_variant_id])->first();
                $product_variant_name = $get_variant_name->value ?? '';
                $get_sub_attribute = Attributes::where(['id' => $product->sub_variant_attribute_id])->first();
                $sub_attribute_title = $get_sub_attribute->title ?? '';
                $get_sub_variant_name = ProductSubVariants::where(['id' => $value->product_sub_variant_id])->first();
                $product_sub_variant_name = $get_sub_variant_name->value ?? '';
            } else {
                $finalPrice = calculateProductPrice($originalPrice, $salePrice);
            }

            $sub_totals += $finalPrice * $value->quantity;
        } 

        if ($request->isMethod('post')) {

        }

        $total_arr = [
            'sub_totals' => $sub_totals,
            'discount'   => $discount,
            'total'      => $sub_totals - $discount,
        ];
        $get_countries  = Country::whereNull('is_delete')->orderBy('id', 'desc')->get();
        
        return view('front.checkout.checkout', compact('total_arr','get_countries') );
    }

    public function save_address(Request $request){
        
        if ($request->isMethod('post')) {


            $customer = Auth::guard('customers')->user();

            $req_fields = array();
            $errormsg = array();

            $req_fields['billing_address_title']    = "required";
            $req_fields['billing_first_name']       = "required";
            $req_fields['billing_last_name']        = "required";
            //$req_fields['billing_email']            = 'required|email|unique:customer_address,email';
            $req_fields['billing_email']            = 'required|email';
            $req_fields['billing_phone']            = "required";
            $req_fields['billing_country_id']       = "required";
            $req_fields['billing_state_id']         = "required";
            $req_fields['billing_city_id']          = "required";
            $req_fields['billing_street_address']   = "required";
            $req_fields['billing_pincode']          = "required";


            $errormsg['billing_address_title'] = translate("Address");
            $errormsg['billing_first_name'] = translate("First Name");
            $errormsg['billing_last_name'] = translate("Last Name");
            $errormsg['billing_email'] = translate("Email");
            $errormsg['billing_phone'] = translate("Phone");
            $errormsg['billing_country_id'] = translate("Country");
            $errormsg['billing_state_id'] = translate("State");
            $errormsg['billing_city_id'] = translate("City");
            $errormsg['billing_street_address'] = translate("Street address");
            $errormsg['billing_pincode'] = translate("Pin Code");

            $validation = Validator::make(
                $request->all(),
                $req_fields,
                [
                    'required' => 'The :attribute field is required.',
                ],
                $errormsg
            );

            if ($validation->fails()) {
                return response()->json(['error' => $validation->getMessageBag()->toArray() ]);
            }

            $message    = translate("Add Successfully");
            $status     = "success";
            $CustomerAddress = new CustomerAddress();
            $CustomerAddress->customer_id       = $customer->id;
            $CustomerAddress->address_title     = $request->billing_address_title;
            $CustomerAddress->first_name        = $request->billing_first_name;
            $CustomerAddress->last_name         = $request->billing_last_name;
            $CustomerAddress->email             = $request->billing_email;
            $CustomerAddress->phone             = $request->billing_phone;
            $CustomerAddress->company_name      = $request->billing_company_name ?? '';
            $CustomerAddress->country           = $request->billing_country_id;
            $CustomerAddress->state             = $request->billing_state_id;
            $CustomerAddress->city              = $request->billing_city_id;
            $CustomerAddress->street_address    = $request->billing_street_address;
            $CustomerAddress->pincode           = $request->billing_pincode;
            /*$get_customer_address = CustomerAddress::where([ 'customer_id' => $customer->id ])->count();
            if( !$get_customer_address ){
                $CustomerAddress->address_title = 'Default Address';
            }*/ 
            $CustomerAddress->save();

            return response()->json([$status => $message,'error'=>false]); 

        }
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $amount = $request->amount; // in cents, e.g. 5000 = $50.00

            $paymentIntent = PaymentIntent::create([
                'amount'   => $amount,
                'currency' => 'nzd',
                'payment_method_types' => ['card'],
            ]);

            return response()->json([
                'success'       => true,
                'clientSecret'  => $paymentIntent->client_secret,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Pure subtotal calculation shared by the checkout page, the coupon
    // apply/remove endpoints, and place_order() - a single source of truth so the
    // amount a coupon is validated/discounted against always matches what's
    // actually in the cart, instead of three separately-maintained copies of this loop.
    private function calculateCartSubtotal($carts)
    {
        $sub_totals = 0;
        foreach ($carts as $value) {
            $product = Products::where('id', $value->product_id)->first();
            if (!$product) continue;

            $originalPrice = $product->regular_price;
            $salePrice     = $product->discount_price;
            if ($product->product_type == 'variable') {
                $variant_item = ProducSubtVariantItems::where(['product_id' => $value->product_id]);
                if ($value->product_variant_id != '')     $variant_item->where(['product_variant_id'     => $value->product_variant_id]);
                if ($value->product_sub_variant_id != '') $variant_item->where(['product_sub_variant_id' => $value->product_sub_variant_id]);
                $variant_item = $variant_item->first();
                $finalPrice   = $variant_item ? calculateProductPrice($variant_item->price) : 0;
            } else {
                $finalPrice = calculateProductPrice($originalPrice, $salePrice);
            }
            $sub_totals += $finalPrice * $value->quantity;
        }
        return $sub_totals;
    }

    // Mirrors calculateCartSubtotal()'s structure/resolution exactly, but sums
    // weight (kg) instead of price - shared by checkout(), the coupon and
    // select-shipping AJAX endpoints, and place_order() so the weight a shipping
    // method is priced against always matches what's actually in the cart.
    private function calculateCartWeight($carts): float
    {
        $totalWeight = 0;
        foreach ($carts as $value) {
            $product = Products::where('id', $value->product_id)->first();
            if (!$product) continue;

            $weight = null;
            if ($product->product_type == 'variable') {
                $variant_item = ProducSubtVariantItems::where(['product_id' => $value->product_id]);
                if ($value->product_variant_id != '')     $variant_item->where(['product_variant_id'     => $value->product_variant_id]);
                if ($value->product_sub_variant_id != '') $variant_item->where(['product_sub_variant_id' => $value->product_sub_variant_id]);
                $variant_item = $variant_item->first();
                $weight = $variant_item->weight ?? null;
            }

            // Fall back to the base product's weight if there's no sub-variant
            // weight (simple products, or a variable product whose sub-variant
            // row simply doesn't have a weight set). Never crash on unweighed
            // legacy products - treat missing weight as 0.
            if ($weight === null) {
                $weight = $product->weight ?? null;
            }

            $totalWeight += ((float) ($weight ?? 0)) * $value->quantity;
        }
        return $totalWeight;
    }

    // Active, currently-priceable shipping methods for a given cart weight,
    // cheapest first. Methods that return null from calculateCost() (e.g. a
    // weight-based method with no brackets configured) are filtered out entirely
    // rather than surfaced as a $0 option.
    private function getAvailableShippingMethods(float $totalWeightKg): array
    {
        $methods   = ShippingMethod::active()->get();
        $available = [];

        foreach ($methods as $method) {
            $cost = $method->calculateCost($totalWeightKg);
            if ($cost === null) continue;

            $available[] = [
                'id'    => $method->id,
                'title' => $method->title,
                'cost'  => $cost,
            ];
        }

        usort($available, fn ($a, $b) => $a['cost'] <=> $b['cost']);

        return $available;
    }

    // Reads the session-selected shipping method id and validates it against the
    // methods currently available for this cart (mirrors Coupon::resolveFromSession's
    // "re-validate every time" approach). If it's missing or no longer available
    // (admin deactivated it, cart weight moved it out of every bracket, or this is
    // the first visit), default to the cheapest available method and persist that
    // choice back into the session - shared by checkout(), the coupon endpoints,
    // select-shipping, and place_order() so they never disagree on what's selected.
    private function resolveSelectedShippingMethodId(array $shippingMethods)
    {
        $selectedId = session('selected_shipping_method_id');
        $isValid    = $selectedId !== null && collect($shippingMethods)->contains('id', $selectedId);

        if (!$isValid) {
            $selectedId = $shippingMethods[0]['id'] ?? null;
            if ($selectedId !== null) {
                session(['selected_shipping_method_id' => $selectedId]);
            } else {
                session()->forget('selected_shipping_method_id');
            }
        }

        return $selectedId;
    }

    // Looks up the cost for a resolved selection - 0 when there's no selection
    // (no shipping methods configured yet), never trusting anything client-supplied.
    private function shippingChargeFor(array $shippingMethods, $selectedId): float
    {
        if ($selectedId === null) return 0;
        $selected = collect($shippingMethods)->firstWhere('id', $selectedId);
        return $selected['cost'] ?? 0;
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required']);

        $customer   = Auth::guard('customers')->user();
        $get_carts  = Cart::with('product')->where('customer_id', $customer->id)->get();
        $sub_totals = $this->calculateCartSubtotal($get_carts);

        $result = Coupon::attemptApply($request->code, $sub_totals);
        if (!$result['success']) {
            return response()->json($result);
        }

        $totalWeight     = $this->calculateCartWeight($get_carts);
        $shippingMethods = $this->getAvailableShippingMethods($totalWeight);
        $selectedId      = $this->resolveSelectedShippingMethodId($shippingMethods);
        $shipping_charge = $this->shippingChargeFor($shippingMethods, $selectedId);

        $taxable = $sub_totals - $result['discount'] + $shipping_charge;
        $gst     = calculateGstAmount($taxable);

        return response()->json($result + [
            'sub_total' => round($sub_totals, 2),
            'shipping'  => round($shipping_charge, 2),
            'gst'       => $gst,
            'total'     => round($taxable + $gst, 2),
        ]);
    }

    public function removeCoupon(Request $request)
    {
        session()->forget('applied_coupon');

        $customer   = Auth::guard('customers')->user();
        $get_carts  = Cart::with('product')->where('customer_id', $customer->id)->get();
        $sub_totals = $this->calculateCartSubtotal($get_carts);

        $totalWeight     = $this->calculateCartWeight($get_carts);
        $shippingMethods = $this->getAvailableShippingMethods($totalWeight);
        $selectedId      = $this->resolveSelectedShippingMethodId($shippingMethods);
        $shipping_charge = $this->shippingChargeFor($shippingMethods, $selectedId);

        $taxable = $sub_totals + $shipping_charge;
        $gst     = calculateGstAmount($taxable);

        return response()->json([
            'success'   => true,
            'sub_total' => round($sub_totals, 2),
            'discount'  => 0,
            'shipping'  => round($shipping_charge, 2),
            'gst'       => $gst,
            'total'     => round($taxable + $gst, 2),
        ]);
    }

    // ─── AJAX: customer picks a shipping method on the checkout page ───────────────
    public function selectShipping(Request $request)
    {
        $customer   = Auth::guard('customers')->user();
        $get_carts  = Cart::with('product')->where('customer_id', $customer->id)->get();
        $sub_totals = $this->calculateCartSubtotal($get_carts);

        $totalWeight     = $this->calculateCartWeight($get_carts);
        $shippingMethods = $this->getAvailableShippingMethods($totalWeight);

        $validation = Validator::make($request->all(), [
            'shipping_method_id' => [
                'required',
                function ($attribute, $value, $fail) use ($shippingMethods) {
                    if (!collect($shippingMethods)->contains('id', $value)) {
                        $fail(translate('Selected shipping method is not available.'));
                    }
                },
            ],
        ]);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => $validation->errors()->first()]);
        }

        session(['selected_shipping_method_id' => (int) $request->shipping_method_id]);

        [$coupon, $discount] = Coupon::resolveFromSession($sub_totals);
        $shipping_charge = $this->shippingChargeFor($shippingMethods, (int) $request->shipping_method_id);

        $taxable = $sub_totals - $discount + $shipping_charge;
        $gst     = calculateGstAmount($taxable);
        $total   = $taxable + $gst;

        return response()->json([
            'success'   => true,
            'sub_total' => round($sub_totals, 2),
            'discount'  => round($discount, 2),
            'code'      => $coupon->code ?? null,
            'shipping'  => round($shipping_charge, 2),
            'gst'       => $gst,
            'total'     => round($total, 2),
        ]);
    }

    // ─── Final Checkout: Save Order after Stripe confirms payment ─────────────────
    public function checkout(Request $request)
    {
        $customer = Auth::guard('customers')->user();
        $get_carts = Cart::with('product')->where('customer_id', $customer->id)->latest()->get();

        if ($get_carts->isEmpty()) {
            return redirect()->route('shop')->withErrors(['error' => translate('Your cart is empty. Please add some products before checkout.')]);
        }

        $sub_totals = $this->calculateCartSubtotal($get_carts);
        [$coupon, $discount] = Coupon::resolveFromSession($sub_totals);

        $totalWeight        = $this->calculateCartWeight($get_carts);
        $shippingMethods    = $this->getAvailableShippingMethods($totalWeight);
        $selectedShippingId = $this->resolveSelectedShippingMethodId($shippingMethods);
        $shipping_charge    = $this->shippingChargeFor($shippingMethods, $selectedShippingId);

        $taxable = $sub_totals - $discount + $shipping_charge;
        $gst     = calculateGstAmount($taxable);
        $total   = $taxable + $gst;

        $total_arr = [
            'sub_totals'                  => $sub_totals,
            'discount'                    => $discount,
            'gst'                         => $gst,
            'total'                       => $total,
            'coupon_code'                 => $coupon->code ?? null,
            'shipping'                    => $shipping_charge,
            'shipping_methods'            => $shippingMethods,
            'selected_shipping_method_id' => $selectedShippingId,
        ];
        $get_countries = Country::whereNull('is_delete')->orderBy('id', 'desc')->get();

        $get_addresses = CustomerAddress::where('customer_id', $customer->id)
                                        ->latest()
                                        ->get();

        return view('front.checkout.checkout', compact('total_arr', 'get_countries', 'get_addresses'));
    }

    public function place_order(Request $request)
    {
        if ($request->isMethod('post')) {

            $customer = Auth::guard('customers')->user();

            $sameAddress = $request->has('same_as_shipping_address');
            $rules = [
                'billing_first_name'        => 'required',
                'billing_last_name'         => 'required',
                'billing_email'             => 'required|email',
                'billing_phone'             => 'required',
                'billing_country_id'        => 'required',
                'billing_state_id'          => 'required',
                'billing_city_id'           => 'required',
                'billing_street_address'    => 'required',
                // 'payment_methode'           => 'required',
                // 'payment_intent_id'         => 'required_if:payment_methode,stripe',
            ];

            if( $customer->customer_type == 'individual' ){
                $rules['payment_methode'] = 'required';
                $rules['payment_intent_id'] = 'required_if:payment_methode,stripe';
            }



            // ── Shipping rules (only if checkbox is NOT checked) ──────────────────
            if (!$sameAddress) {
                $rules['shipping_first_name']       = 'required';
                $rules['shipping_last_name']        = 'required';
                $rules['shipping_email']            = 'required|email';
                $rules['shipping_phone']            = 'required';
                $rules['shipping_country_id']       = 'required';
                $rules['shipping_state_id']         = 'required';
                $rules['shipping_city_id']          = 'required';
                $rules['shipping_street_address']   = 'required';
            }


            // ── Custom attribute names ────────────────────────────────────────────
            $attributes = [
                'billing_first_name'        => 'Billing First Name',
                'billing_last_name'         => 'Billing Last Name',
                'billing_email'             => 'Billing Email',
                'billing_phone'             => 'Billing Phone',
                'billing_country_id'        => 'Billing Country',
                'billing_state_id'          => 'Billing State',
                'billing_city_id'           => 'Billing City',
                'billing_street_address'    => 'Billing Street Address',
                'shipping_first_name'       => 'Shipping First Name',
                'shipping_last_name'        => 'Shipping Last Name',
                'shipping_email'            => 'Shipping Email',
                'shipping_phone'            => 'Shipping Phone',
                'shipping_country_id'       => 'Shipping Country',
                'shipping_state_id'         => 'Shipping State',
                'shipping_city_id'          => 'Shipping City',
                'shipping_street_address'   => 'Shipping Street Address',
            ];

            $validation = Validator::make(
                $request->all(),
                $rules,
                ['required' => 'The :attribute field is required.'],
                $attributes
            );

            if ($validation->fails()) {
                return response()->json(['error' => $validation->getMessageBag()->toArray(), 'login_customer' => $customer]);
            }

            // ── If checkbox checked, copy billing → shipping ───────────────────────
            if ($sameAddress) {
                $request->merge([
                    'shipping_first_name'       => $request->billing_first_name,
                    'shipping_last_name'        => $request->billing_last_name,
                    'shipping_email'            => $request->billing_email,
                    'shipping_phone'            => $request->billing_phone,
                    'shipping_company_name'     => $request->billing_company_name,
                    'shipping_country_id'       => $request->billing_country_id,
                    'shipping_state_id'         => $request->billing_state_id,
                    'shipping_city_id'          => $request->billing_city_id,
                    'shipping_street_address'   => $request->billing_street_address,
                ]);
            }

            if( $customer->customer_type == 'individual' ){
                // ── Verify Stripe Payment ────────────────────────────────────────────
                if ($request->payment_methode == 'stripe') {
                    try {
                        Stripe::setApiKey(config('services.stripe.secret'));
                        $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

                        if ($paymentIntent->status !== 'succeeded') {
                            return response()->json(['error' => ['stripe' => ['Payment not completed. Please try again.']]]);
                        }
                    } catch (\Exception $e) {
                        return response()->json(['error' => ['stripe' => [$e->getMessage()]]]);
                    }
                }
                 
                // ── Verify & store full Stripe response ──────────────────────────────────────
                $stripeData = [];

                if ($request->payment_methode == 'stripe') {
                    try {
                        Stripe::setApiKey(config('services.stripe.secret'));
                        $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

                        if ($paymentIntent->status !== 'succeeded') {
                            return response()->json([
                                'error' => ['stripe' => ['Payment not completed. Please try again.']]
                            ]);
                        }

                        // Store what you need from Stripe response
                        /*$stripeData = [
                            'stripe_payment_response'   => json_encode($paymentIntent->toArray()),
                        ];*/
                        $stripeData = [
                            'payment_intent_id'         => $paymentIntent->id,
                            'stripe_payment_method_id'  => $paymentIntent->payment_method,
                            'stripe_currency'           => $paymentIntent->currency,
                            'stripe_amount_received'    => $paymentIntent->amount_received,
                            'stripe_payment_response'   => json_encode($paymentIntent->toArray()),
                        ];

                    } catch (\Exception $e) {
                        return response()->json([
                            'error' => ['stripe' => [$e->getMessage()]]
                        ]);
                    }
                }

            }

            $customer = Auth::guard('customers')->user();
            $get_carts = Cart::with('product')->where('customer_id', $customer->id)->latest()->get();

            if ($get_carts->isEmpty()) {
                return response()->json(['error' => ['cart' => [translate('Your cart is empty.')]]]);
            }

            // ── Save Order ────────────────────────────────────────────────────────────────
            $order = new Order();

            // Billing
            $order->billing_first_name      = $request->billing_first_name;
            $order->billing_last_name       = $request->billing_last_name;
            $order->billing_email           = $request->billing_email;
            $order->billing_phone           = $request->billing_phone;
            $order->billing_company_name    = $request->billing_company_name;
            $order->billing_country_id      = $request->billing_country_id;
            $order->billing_state_id        = $request->billing_state_id;
            $order->billing_city_id         = $request->billing_city_id;
            $order->billing_street_address  = $request->billing_street_address;

            // Shipping — use billing values if same_as_shipping_address is checked
            $sameAddress = $request->has('same_as_shipping_address');
            $order->same_as_billing         = $sameAddress ? 'yes' : '';
            $order->shipping_first_name     = $sameAddress ? $request->billing_first_name    : $request->shipping_first_name;
            $order->shipping_last_name      = $sameAddress ? $request->billing_last_name     : $request->shipping_last_name;
            $order->shipping_email          = $sameAddress ? $request->billing_email         : $request->shipping_email;
            $order->shipping_phone          = $sameAddress ? $request->billing_phone         : $request->shipping_phone;
            $order->shipping_company_name   = $sameAddress ? $request->billing_company_name  : $request->shipping_company_name;
            $order->shipping_country_id     = $sameAddress ? $request->billing_country_id    : $request->shipping_country_id;
            $order->shipping_state_id       = $sameAddress ? $request->billing_state_id      : $request->shipping_state_id;
            $order->shipping_city_id        = $sameAddress ? $request->billing_city_id       : $request->shipping_city_id;
            $order->shipping_street_address = $sameAddress ? $request->billing_street_address: $request->shipping_street_address;

            // Payment
            if( $customer->customer_type == 'individual' ){
                $order->payment_method          = $request->payment_methode;
                $order->payment_intent_id       = $request->payment_intent_id ?? null;
                $order->stripe_payment_response = $stripeData['stripe_payment_response']   ?? null;
            }
            $order->customer_id             = $customer->id;
            $order->order_note              = $request->order_note ?? null;
            $order->save();



            $orderId = $order->id;
            // ── 1. Save Order Products (kw_order_products) ────────────────────────

            $sub_totals = 0;
            $total = 0;
            foreach ($get_carts as $cart) {
                $product = Products::where('id', $cart->product_id)->first();

                if (!$product) continue; // safety check

                $orderProduct = new OrderProduct(); // your OrderProduct model
                $orderProduct->order_id                 = $orderId;
                $orderProduct->customer_id              = $customer->id;
                $orderProduct->product_id               = $product->id;
                $orderProduct->product_type             = $product->product_type ?? null;
                $orderProduct->image                    = resolve_cart_item_image($cart->product_id, $cart->product_variant_id, $product->image);
                $orderProduct->title                    = $product->title ?? null;
                $orderProduct->sku                      = $product->sku ?? null;
                $orderProduct->quantity                 = $cart->quantity;
                $orderProduct->short_decripation        = $product->short_decripation ?? null;
                $orderProduct->description              = $product->description ?? null;

                $get_category = Categories::where(ConditionQuery())
                        ->where('id', $product->category)
                        ->first();

                if ($get_category) {
                    $orderProduct->category_id = $product->category ?? null;
                    $orderProduct->category = $get_category->title ?? '';
                    // TOP CATEGORY
                    if ($get_category->top_level_category_id) {
                        $top_category = Categories::select('title')
                            ->where(ConditionQuery())
                            ->where('id', $get_category->top_level_category_id)
                            ->first();

                        $orderProduct->top_level_category_id    = $get_category->top_level_category_id ?? null;
                        $orderProduct->top_level_category       = $top_category->title ?? '';
                    }

                    // MID CATEGORY
                    if ($get_category->mid_level_category_id) {
                        $mid_category = Categories::select('title')
                            ->where(ConditionQuery())
                            ->where('id', $get_category->mid_level_category_id)
                            ->first();
                        $orderProduct->mid_level_category_id    = $get_category->mid_level_category_id ?? null;
                        $orderProduct->mid_level_category       = $mid_category->title ?? '';
                    }
                }

                $originalPrice = $product->regular_price;
                $salePrice     = $product->discount_price;

                $finalPrice = calculateProductPrice($originalPrice, $salePrice);

                if ($product->product_type == 'variable') {
                    $variant_item = ProducSubtVariantItems::where([ 'product_id' => $cart->product_id]);
                    if ($cart->product_variant_id != '') $variant_item->where(['product_variant_id'     => $cart->product_variant_id]);
                    if ($cart->product_sub_variant_id != '') $variant_item->where(['product_sub_variant_id' => $cart->product_sub_variant_id]);
                    $variant_item = $variant_item->first();
                    $finalPrice   = $variant_item ? calculateProductPrice($variant_item->price) : 0;

                    /* Variant */
                    $orderProduct->product_variant_id = $cart->product_variant_id ?? null;
                    $get_attribute = Attributes::where([ 'id' => $product->attribute_id ])->first();
                    $orderProduct->attribute_title = $get_attribute->title ?? null;

                    $get_variant_name = ProductVariants::where(['id' => $cart->product_variant_id ])->first();
                    $orderProduct->product_variant_name = $get_variant_name->value ?? null;

                    /* Sub Variant */
                    $orderProduct->product_sub_variant_id = $cart->product_sub_variant_id ?? null;
                    $get_sub_attribute = Attributes::where([ 'id' => $product->sub_variant_attribute_id ])->first();
                    $orderProduct->sub_attribute_title = $get_sub_attribute->title ?? null;

                    $get_sub_variant_name = ProductSubVariants::where([ 'id' => $cart->product_sub_variant_id])->first();
                    $orderProduct->product_sub_variant_name = $get_sub_variant_name->value ?? null;
                } 
                
                $orderProduct->price        = $finalPrice;
                /*$orderProduct->shipping_id  = $product->shipping_id ?? null;
                $orderProduct->shipping     = $shippingName;*/
                $orderProduct->save();

                // ── ✅ Inventory Deduction ────────────────────────────────────────────────
                if ($product->product_type == 'variable') {

                    // Deduct variant stock from kw_product_sub_variant_items
                    $variant_stock_query = ProducSubtVariantItems::where('product_id', $cart->product_id);
                    if (!empty($cart->product_variant_id))     $variant_stock_query->where('product_variant_id',     $cart->product_variant_id);
                    if (!empty($cart->product_sub_variant_id)) $variant_stock_query->where('product_sub_variant_id', $cart->product_sub_variant_id);
                    $variant_stock = $variant_stock_query->first();

                    if ($variant_stock) {
                        $new_stock = max(0, $variant_stock->stock - $cart->quantity);
                        $variant_stock->stock = $new_stock;
                        // Mark inactive if out of stock
                        if ($new_stock == 0) {
                            $variant_stock->status = 'inactive';
                        }
                        $variant_stock->save();
                    }
                } else {
                    // Deduct simple product quantity from kw_products
                    $new_qty = max(0, $product->quantity - $cart->quantity);
                    Products::where('id', $product->id)->update(['quantity' => $new_qty]);
                }
                // ── End Inventory ──────────

                $sub_totals += $finalPrice * $cart->quantity;
            }

            // ── Coupon: re-validate and lock the row so two concurrent orders can't
            // both squeeze past a coupon's usage_limit ─────────────────────────────
            $coupon   = null;
            $discount = 0;
            DB::transaction(function () use (&$coupon, &$discount, $sub_totals) {
                $applied = session('applied_coupon');
                if (!$applied) {
                    return;
                }
                $c = Coupon::where('id', $applied['id'])->lockForUpdate()->first();
                if (!$c) {
                    return;
                }
                [$ok, ] = $c->isRedeemable($sub_totals);
                if (!$ok) {
                    return;
                }
                $discount = $c->calculateDiscount($sub_totals);
                $c->increment('used_count');
                $coupon = $c;
            });
            session()->forget('applied_coupon');

            // ── Shipping: re-validate & re-calculate server-side from the current
            // cart weight + active methods - never trust a client-submitted cost ────
            $totalWeight        = $this->calculateCartWeight($get_carts);
            $shippingMethods    = $this->getAvailableShippingMethods($totalWeight);
            $selectedShippingId = $this->resolveSelectedShippingMethodId($shippingMethods);
            $shipping_charge    = $this->shippingChargeFor($shippingMethods, $selectedShippingId);
            session()->forget('selected_shipping_method_id');

            $taxable = $sub_totals - $discount + $shipping_charge;
            $gst     = calculateGstAmount($taxable);
            $total   = $taxable + $gst;

            // ── 2. Save Order Meta (kw_order_meta) ───────────────────────────────
            $metaData = [
                'sub_total'          => $sub_totals,
                'discount'           => $discount,
                'gst'                => $gst,
                'total'              => $total,
                'shipping_method_id' => $selectedShippingId,
                'shipping'           => $shipping_charge,
            ];
            if ($coupon) {
                $metaData['coupon_code'] = $coupon->code;
            }
            if( $customer->customer_type == 'individual' ){
                $metaData['currency']  = $stripeData['stripe_currency']  ?? '';

            }

            foreach ($metaData as $key => $value) {
                $meta = new OrderMeta();
                $meta->order_id     = $orderId;
                $meta->customer_id  = $customer->id;
                $meta->guest_id     = null;
                $meta->meta_key     = $key;
                $meta->meta_value   = $value;
                $meta->save();
            }

            // ── 3. Clear Cart ─────────────────────────────────────────────────────
            Cart::where('customer_id', $customer->id)->delete(); 


            // ── 4. Send Order Confirmation Email ─────────────────────────────────────────
            $order_products_mail = OrderProduct::where('order_id', $orderId)->get();

            $meta_rows  = OrderMeta::where('order_id', $orderId)->get();
            $order_meta = [];
            foreach ($meta_rows as $meta) {
                $order_meta[$meta->meta_key] = $meta->meta_value;
            }

            // Location names
            $billing_country  = Country::find($order->billing_country_id);
            $billing_state    = State::find($order->billing_state_id);
            $billing_city     = City::find($order->billing_city_id);
            $shipping_country = Country::find($order->shipping_country_id);
            $shipping_state   = State::find($order->shipping_state_id);
            $shipping_city    = City::find($order->shipping_city_id);

            $data = [
                'subject'          => 'New Order #' . $orderId . ' - ' . config('app.name'),
                'email'            => $order->billing_email,
                'name'             => $order->billing_first_name . ' ' . $order->billing_last_name,
                'page'             => 'email.front.order.new-order',
                'order'            => $order,
                'order_meta'       => $order_meta,
                'order_products'   => $order_products_mail,
                'billing_country'  => $billing_country,
                'billing_state'    => $billing_state,
                'billing_city'     => $billing_city,
                'shipping_country' => $shipping_country,
                'shipping_state'   => $shipping_state,
                'shipping_city'    => $shipping_city,
            ];

            Mail::send($data['page'], $data, function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });

            return response()->json(['success' => translate('Order placed successfully!'), 'error' => false]);
        }
    }

    public function order_products(){

        $customer = Auth::guard('customers')->user();
        $get_carts = Cart::with('product')->where('customer_id', $customer->id)->latest()->get();
        $orderId = 3;

        // ── 2. Save Order Products (kw_order_products) ────────────────────────

        $sub_totals = 0;
        $discount   = 10;
        $total = 0;    
        foreach ($get_carts as $cart) {
            $product = Products::where('id', $cart->product_id)->first();
            $orderProduct = new OrderProduct(); // your OrderProduct model
            $orderProduct->order_id                 = $orderId;
            $orderProduct->customer_id              = $customer->id;
            $orderProduct->product_id               = $product->id;
            $orderProduct->product_type             = $product->product_type ?? null;
            $orderProduct->image                    = resolve_product_image($product->image);
            $orderProduct->title                    = $product->title ?? null;
            $orderProduct->sku                      = $product->sku ?? null;
            $orderProduct->quantity                 = $cart->quantity;
            $orderProduct->short_decripation        = $product->short_decripation ?? null;
            $orderProduct->description              = $product->description ?? null;

            $get_category = Categories::where(ConditionQuery())
                    ->where('id', $product->category)
                    ->first();

            if ($get_category) {
                $orderProduct->category_id = $product->category ?? null;
                $orderProduct->category = $get_category->title ?? '';
                // TOP CATEGORY
                if ($get_category->top_level_category_id) {
                    $top_category = Categories::select('title')
                        ->where(ConditionQuery())
                        ->where('id', $get_category->top_level_category_id)
                        ->first();

                    $orderProduct->top_level_category_id    = $get_category->top_level_category_id ?? null;
                    $orderProduct->top_level_category       = $top_category->title ?? '';
                }

                // MID CATEGORY
                if ($get_category->mid_level_category_id) {
                    $mid_category = Categories::select('title')
                        ->where(ConditionQuery())
                        ->where('id', $get_category->mid_level_category_id)
                        ->first();
                    $orderProduct->mid_level_category_id    = $get_category->mid_level_category_id ?? null;
                    $orderProduct->mid_level_category       = $mid_category->title ?? '';
                }
            }

            $originalPrice = $product->regular_price;
            $salePrice     = $product->discount_price;

            $finalPrice = calculateProductPrice($originalPrice, $salePrice);

            if ($product->product_type == 'variable') {
                $variant_item = ProducSubtVariantItems::where([ 'product_id' => $cart->product_id]);
                if ($cart->product_variant_id != '') $variant_item->where(['product_variant_id'     => $cart->product_variant_id]);
                if ($cart->product_sub_variant_id != '') $variant_item->where(['product_sub_variant_id' => $cart->product_sub_variant_id]);
                $variant_item = $variant_item->first();
                $finalPrice   = $variant_item ? calculateProductPrice($variant_item->price) : 0;

                /* Variant */
                $orderProduct->product_variant_id = $cart->product_variant_id ?? null;
                $get_attribute = Attributes::where([ 'id' => $product->attribute_id ])->first();
                $orderProduct->attribute_title = $get_attribute->title ?? null;

                $get_variant_name = ProductVariants::where(['id' => $cart->product_variant_id ])->first();
                $orderProduct->product_variant_name = $get_variant_name->value ?? null;

                /* Sub Variant */
                $orderProduct->product_sub_variant_id = $cart->product_sub_variant_id ?? null;
                $get_sub_attribute = Attributes::where([ 'id' => $product->sub_variant_attribute_id ])->first();
                $orderProduct->sub_attribute_title = $get_sub_attribute->title ?? null;

                $get_sub_variant_name = ProductSubVariants::where([ 'id' => $cart->product_sub_variant_id])->first();
                $orderProduct->product_sub_variant_name = $get_sub_variant_name->value ?? null;
            } 
            
            $orderProduct->price        = $finalPrice;
            /*$orderProduct->shipping_id  = $product->shipping_id ?? null;
            $orderProduct->shipping     = $shippingName;*/
            $orderProduct->save();

            $sub_totals += $finalPrice * $cart->quantity;

        }
        $total = $sub_totals - $discount;

        
        // ── 3. Save Order Meta (kw_order_meta) ───────────────────────────────
        $metaData = [
            'sub_total'             => $sub_totals,
            'discount'              => $discount,
            'total'                 => $total,
            'stripe_intent_id'      => $stripeData['payment_intent_id']       ?? '',
            'stripe_currency'       => $stripeData['stripe_currency']          ?? '',
            'stripe_amount_received'=> $stripeData['stripe_amount_received']   ?? '',
        ];

        foreach ($metaData as $key => $value) {
            $meta = new OrderMeta();
            $meta->order_id     = $orderId;
            $meta->customer_id  = $customer->id;
            $meta->guest_id     = null;
            $meta->meta_key     = $key;
            $meta->meta_value   = $value;
            $meta->save();
        }

        // ── 4. Clear Cart ─────────────────────────────────────────────────────
        //Cart::where('customer_id', $customer->id)->delete();

        /*return redirect('/order_products')->with(['success' => 'Order Product']);
        echo "<pre>";
        print_r($get_carts);
        echo "</pre>";*/

        echo "Yes";
        die();
    }


}