<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Customers;
use App\Models\CustomerGroups;
use App\Models\Categories;
use App\Models\Products;
use App\Models\ProductGallery;
use App\Models\Attributes;
use App\Models\ProductVariants;
use App\Models\ProductVariantImages;
use App\Models\ProductSubVariants;
use App\Models\ProducSubtVariantItems;
use App\Models\Cart;
use App\Models\Wishlist;
use App\Models\Coupon;


class CartController extends Controller
{

    public function index(Request $request)
    {

        $customer = Auth::guard('customers')->user();
        $data = $this->buildCartData($customer->id);
        return view('front.cart.cart', $data);
    }

    public function add_to_cart(Request $request)
    {
        if (!$request->isMethod('post')) {

            return response()->json([
                'error'    => true,
                'message'  => 'Invalid request',
                'redirect' => false,
            ]);
        }

        // LOGIN CHECK
        if (!Auth::guard('customers')->check()) {

            return response()->json([
                'error'    => true,
                'message'  => 'Please login first',
                'redirect' => true,
            ]);
        }

        $product_id = checkDecrypt($request->product_id);

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER
        |--------------------------------------------------------------------------
        */

        $customer = Auth::guard('customers')->user();
        $product_qty = $request->product_qty ?? 1;
        // PRODUCT
        $get_product = Products::select(
                'id',
                'product_type'
            )
            ->where('id', $product_id)
            ->first();

        if (!$get_product) {
            return response()->json([
                'error'   => true,
                'message' => 'Product not found',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | VARIABLE PRODUCT VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($get_product->product_type == 'variable') {

            $rules    = [];
            $messages = [];

            // Explicitly check variant_name1 and variant_name2 are selected.
            // (There used to also be a required|integer check on attribute_id1/2 here,
            // but those values are never used beyond this validation — they were fed
            // from ProductSubVariants.product_variant_id, a nullable scoping FK that's
            // legitimately empty whenever sizes are shared across all colors — so it
            // broke Add to Cart / Buy Now for any product with shared sub-variants.)
            $variantKeys = ['variant_name1', 'variant_name2'];

            foreach ($variantKeys as $key) {

                // Only validate if the input exists in the request
                if ($request->has($key)) {
                    $rules[$key] = 'required';
                    $messages["$key.required"] = 'Please select product variant';
                }
            }

            if (!empty($rules)) {

                $validation = Validator::make(
                    $request->all(),
                    $rules,
                    $messages
                );

                if ($validation->fails()) {
                    return response()->json([
                        'error'   => true,
                        'message' => 'Please select all product variants',
                        'data'    => $validation->errors(),
                    ]);
                }
            }


            $variant_item = ProducSubtVariantItems::where(['product_id' => $product_id]);
            if ($request->variant_name1 != '') {
                $variant_item->where(['product_variant_id' => $request->variant_name1]);
            }
            if ($request->variant_name2 != '') {
                $variant_item->where(['product_sub_variant_id' => $request->variant_name2]);
            }
            $variant_item = $variant_item->first();

            if (!$variant_item) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Selected variant not found',
                ]);
            }

            if ($variant_item->stock <= 0) {
                return response()->json([
                    'error'   => true,
                    'message' => 'This variant is out of stock',
                ]);
            }

            // Check existing cart qty + new qty doesn't exceed stock
            $existing_cart = Cart::where([
                'product_id'  => $product_id,
                'customer_id' => $customer->id,
            ]);
            if ($request->variant_name1 != '') $existing_cart->where('product_variant_id', $request->variant_name1);
            if ($request->variant_name2 != '') $existing_cart->where('product_sub_variant_id', $request->variant_name2);
            $existing_cart = $existing_cart->first();

            $current_qty = $existing_cart ? $existing_cart->quantity : 0;

            if (($current_qty + $product_qty) > $variant_item->stock) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Only ' . $variant_item->stock . ' item(s) available. You already have ' . $current_qty . ' in cart.',
                ]);
            }

        } else {
            // SIMPLE PRODUCT — stock from products table
            $full_product = Products::where('id', $product_id)->first();

            if ($full_product->quantity <= 0) {
                return response()->json([
                    'error'   => true,
                    'message' => 'This product is out of stock',
                ]);
            }

            $existing_cart = Cart::where([
                'product_id'  => $product_id,
                'customer_id' => $customer->id,
            ])->first();

            $current_qty = $existing_cart ? $existing_cart->quantity : 0;

            if (($current_qty + $product_qty) > $full_product->quantity) {
                return response()->json([
                    'error'   => true,
                    'message' => 'Only ' . $full_product->quantity . ' item(s) available. You already have ' . $current_qty . ' in cart.',
                ]);
            }
        }


        
        /*
        |--------------------------------------------------------------------------
        | CHECK CART ITEM
        |--------------------------------------------------------------------------
        */

        $cart_check = Cart::where([ 'product_id'  => $product_id, 'customer_id' => $customer->id ]);
        if ($get_product->product_type == 'variable') {
            if( $request->variant_name1 != '' ){
                $cart_check->where([ 'product_variant_id'  => $request->variant_name1 ]);
            }
            if( $request->variant_name2 != '' ){
                $cart_check->where([ 'product_sub_variant_id'  => $request->variant_name2 ]);
            }
        }
        $cart_check = $cart_check ->first();

        if ($cart_check) {
            $cart_check->increment('quantity', $product_qty);
            $Cart = $cart_check;
        } else {
            $Cart = new Cart();
            $Cart->product_id  = $product_id;
            $Cart->customer_id = $customer->id;
            $Cart->quantity    = $product_qty;
            // BUSINESS CUSTOMER
            if ($customer->customer_type == 'business') {
                $Cart->customer_group_id = $customer->customer_group_id;
            }

        }

        if ($get_product->product_type == 'variable') {
            if( $request->variant_name1 != '' ){
                $Cart->product_variant_id = $request->variant_name1;
            }
            if( $request->variant_name2 != '' ){
                $Cart->product_sub_variant_id = $request->variant_name2;
            }
        }

        $Cart->save();

        /*
        |--------------------------------------------------------------------------
        | UPDATED CART
        |--------------------------------------------------------------------------
        */

        $get_carts = Cart::with('product')
            ->where('customer_id', $customer->id)
            ->latest()
            ->get();

        $html = view('front.cart.cart-items', compact('get_carts'))
            ->render();

        return response()->json([
            'error'      => false,
            'message'    => 'Product added into cart',
            'html'       => $html,
            'count'      => $get_carts->count(),
            'cart_count' => $get_carts->sum('quantity'),
            'redirect'   => false,
        ]);
    }

    // Reusable helper to build cart data
    private function buildCartData($customer_id)
    {
        $get_carts = Cart::with('product')->where('customer_id', $customer_id)->latest()->get();
        $cart_arrs = [];
        $sub_totals = 0;

        foreach ($get_carts as $value) {
            $cart_arr = [];
            $attribute_title = $sub_attribute_title = $product_variant_name = $product_sub_variant_name = '';

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

            $cart_arr['id'] = $value->id;
            $cart_arr['quantity'] = $value->quantity;
            $cart_arr['product_id'] = $value->product_id;
            $cart_arr['title'] = $product->title;
            $cart_arr['slug'] = $product->slug;
            $cart_arr['image'] = resolve_cart_item_image($value->product_id, $value->product_variant_id, $product->image);
            $cart_arr['product_type'] = $product->product_type;
            $cart_arr['finalPrice'] = $finalPrice;
            $cart_arr['item_sub_total'] = $finalPrice * $value->quantity;
            $cart_arr['attribute_title'] = $attribute_title;
            $cart_arr['sub_attribute_title'] = $sub_attribute_title;
            $cart_arr['product_variant_name'] = $product_variant_name;
            $cart_arr['product_sub_variant_name'] = $product_sub_variant_name;

            $cart_arrs[] = $cart_arr;
            $sub_totals += $finalPrice * $value->quantity;
        }

        [$coupon, $discount] = Coupon::resolveFromSession($sub_totals);

        return [
            'cart_arrs' => $cart_arrs,
            'total_arr' => [
                'sub_totals'  => $sub_totals,
                'discount'    => $discount,
                'total'       => $sub_totals - $discount,
                'coupon_code' => $coupon->code ?? null,
            ]
        ];
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required']);
        $customer = Auth::guard('customers')->user();

        // buildCartData() resolves whatever coupon is *currently* in session, so it
        // gives us the pre-apply subtotal for free without duplicating the pricing loop.
        $preData   = $this->buildCartData($customer->id);
        $subtotal  = $preData['total_arr']['sub_totals'];

        $result = Coupon::attemptApply($request->code, $subtotal);

        // Rebuild so the returned totals reflect the coupon just stored in session.
        $data = $this->buildCartData($customer->id);

        return response()->json([
            'error'       => !$result['success'],
            'message'     => $result['message'],
            'totals_html' => view('front.cart.partials.cart_totals', $data)->render(),
        ]);
    }

    public function removeCoupon(Request $request)
    {
        session()->forget('applied_coupon');

        $customer = Auth::guard('customers')->user();
        $data     = $this->buildCartData($customer->id);

        return response()->json([
            'error'       => false,
            'message'     => 'Coupon removed',
            'totals_html' => view('front.cart.partials.cart_totals', $data)->render(),
        ]);
    }

    // Update quantity
    public function updateCart_old(Request $request)
    {
        $customer = Auth::guard('customers')->user();
        $cart_ids = $request->cart_id;
        $quantities = $request->cart_qty;

        foreach ($cart_ids as $index => $cart_id) {
            $qty = max(1, (int)$quantities[$index]);
            Cart::where('id', $cart_id)->where('customer_id', $customer->id)->update(['quantity' => $qty]);
        }

        $data = $this->buildCartData($customer->id);

        // Get raw carts for sidebar (your existing sidebar view uses $get_carts)
        $get_carts = Cart::with('product')->where('customer_id', $customer->id)->latest()->get();


        $html = view('front.cart.partials.cart-page-items', $data)->render();
        $totals = view('front.cart.partials.cart_totals', $data)->render();
        $sidebar     = view('front.cart.cart-items', compact('get_carts'))->render();

        return response()->json([
            'error'        => false,
            'message'      => 'Cart updated successfully',
            'items_html'   => $html,
            'totals_html'  => $totals,
            'sidebar_html' => $sidebar,
            'cart_count'   => $get_carts->sum('quantity'),
        ]);
    }

    public function updateCart(Request $request)
    {
        $customer   = Auth::guard('customers')->user();
        $cart_ids   = $request->cart_id;
        $quantities = $request->cart_qty;
        $errors     = [];

        foreach ($cart_ids as $index => $cart_id) {
            $qty  = max(1, (int)$quantities[$index]);
            $cart = Cart::with('product')->where('id', $cart_id)->where('customer_id', $customer->id)->first();

            if (!$cart) continue;

            $product = $cart->product;

            if ($product->product_type == 'variable') {

                $variant_item = ProducSubtVariantItems::where(['product_id' => $cart->product_id]);
                if ($cart->product_variant_id)     $variant_item->where('product_variant_id', $cart->product_variant_id);
                if ($cart->product_sub_variant_id) $variant_item->where('product_sub_variant_id', $cart->product_sub_variant_id);
                $variant_item = $variant_item->first();

                if ($variant_item && $qty > $variant_item->stock) {
                    $errors[] = '"' . $product->title . '" only has ' . $variant_item->stock . ' item(s) in stock.';
                    $qty = $variant_item->stock; // cap to available stock
                }

            } else {
                if ($qty > $product->quantity) {
                    $errors[] = '"' . $product->title . '" only has ' . $product->quantity . ' item(s) in stock.';
                    $qty = $product->quantity; // cap to available stock
                }
            }

            Cart::where('id', $cart_id)->where('customer_id', $customer->id)->update(['quantity' => $qty]);
        }

        $data      = $this->buildCartData($customer->id);
        $get_carts = Cart::with('product')->where('customer_id', $customer->id)->latest()->get();

        return response()->json([
            'error'        => count($errors) > 0,
            'message'      => count($errors) > 0 ? implode('</br>', $errors) : 'Cart updated successfully',
            'items_html'   => view('front.cart.partials.cart-page-items', $data)->render(),
            'totals_html'  => view('front.cart.partials.cart_totals', $data)->render(),
            'sidebar_html' => view('front.cart.cart-items', compact('get_carts'))->render(),
            'cart_count'   => $get_carts->sum('quantity'),
        ]);
    }

    // Delete single cart item
    public function deleteCart(Request $request)
    {
        $customer = Auth::guard('customers')->user();
        $cart_id = decrypt($request->cart_id);
        Cart::where('id', $cart_id)->where('customer_id', $customer->id)->delete();
        $data = $this->buildCartData($customer->id);
        $get_carts = Cart::with('product')->where('customer_id', $customer->id)->latest()->get();
        $html = view('front.cart.partials.cart-page-items', $data)->render();
        $totals = view('front.cart.partials.cart_totals', $data)->render();
        $sidebar     = view('front.cart.cart-items', compact('get_carts'))->render();

        return response()->json([
            'error'        => false,
            'message'      => 'Cart item deleted successfully',
            'items_html'   => $html,
            'totals_html'  => $totals,
            'sidebar_html' => $sidebar,
            'cart_count'   => $get_carts->sum('quantity'),
        ]);
    }

    // Clear all cart items
    public function clearCart(Request $request)
    {
        $customer = Auth::guard('customers')->user();
        Cart::where('customer_id', $customer->id)->delete();

        $data = $this->buildCartData($customer->id);
        $get_carts = Cart::with('product')->where('customer_id', $customer->id)->latest()->get();
        $html        = view('front.cart.partials.cart-page-items', $data)->render();
        $totals      = view('front.cart.partials.cart_totals', $data)->render();
        $sidebar     = view('front.cart.cart-items', compact('get_carts'))->render();
        return response()->json(['success' => true, 'items_html' => $html, 'totals_html' => $totals]);

        return response()->json([
            'error'        => false,
            'message'      => 'Cart Empty',
            'items_html'   => $html,
            'totals_html'  => $totals,
            'sidebar_html' => $sidebar,
            'cart_count'   => $get_carts->sum('quantity'),
        ]);
    }

}