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
use App\Models\Wishlist;

class WishlistController extends Controller
{

    public function add_to_wishlist(Request $request)
    {
        // LOGIN CHECK — same pattern as add_to_cart
        if (!Auth::guard('customers')->check()) {
            return response()->json([
                'error'    => true,
                'message'  => 'Please login first',
                'redirect' => true,
            ]);
        }

        $productId  = checkDecrypt($request->product_id); // same helper as cart
        $customerId = Auth::guard('customers')->id();
        $product    = Products::findOrFail($productId);

        $wishlist = Wishlist::where('customer_id', $customerId)
                            ->where('product_id', $productId)
                            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $wishlisted = false;
            $message    = 'Removed from wishlist';
        } else {
            Wishlist::create([
                'customer_id'  => $customerId,
                'product_id'   => $productId,
                'product_type' => $product->product_type,
            ]);
            $wishlisted = true;
            $message    = 'Added to wishlist';
        }

        // Total wishlist count for header badge
        $wishlist_count = Wishlist::where('customer_id', $customerId)->count();

        return response()->json([
            'error'          => false,
            'wishlisted'     => $wishlisted,
            'message'        => $message,
            'wishlist_count' => $wishlist_count,
            'redirect'       => false,
        ]);
    }

}