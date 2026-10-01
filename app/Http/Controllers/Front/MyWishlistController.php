<?php
namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use View;
use Mail;
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

class MyWishlistController extends Controller
{

    public function index(Request $request)
    {
        $customer      = Auth::guard('customers')->user();
        $search        = $request->search ?? '';
        $data          = $this->buildWishlistData($customer->id, $search);
        return view('front.mywishlist', $data);
    }

    // Reusable helper — same pattern as buildCartData
    private function buildWishlistData($customer_id, $search = '')
    {
        $query = Wishlist::with('product')
                         ->where('customer_id', $customer_id)
                         ->latest();

        $get_wishlists  = $query->paginate(config('adminconfig.records_per_page'));
        $wishlist_arrs  = [];

        foreach ($get_wishlists as $value) {

            $product = $value->product;

            // Skip if product deleted
            if (!$product) continue;

            // Search filter
            if ($search != '' && !str_contains(strtolower($product->title), strtolower($search))) {
                continue;
            }

            $wishlist_arr = [];
            $attribute_title          = '';
            $sub_attribute_title      = '';
            $product_variant_name     = '';
            $product_sub_variant_name = '';
            $stock_status             = '';
            $originalPrice            = $product->regular_price;
            $salePrice                = $product->discount_price;

            if ($product->product_type == 'variable') {
                // GET MIN PRICE VARIANT
                $variant_item = ProducSubtVariantItems::where('product_id', $product->id)
                                    ->where('status', 'active')
                                    ->where('is_deleted', 0)
                                    ->orderBy('price', 'asc')
                                    ->first();

                $finalPrice    = $variant_item ? calculateProductPrice($variant_item->price) : 0;
                $originalPrice = $variant_item->price ?? 0;
                $salePrice     = null;

                // STOCK STATUS — any variant in stock
                $hasStock = ProducSubtVariantItems::where('product_id', $product->id)
                                ->where('status', 'active')
                                ->where('is_deleted', 0)
                                ->where('stock', '>', 0)
                                ->exists();
                $stock_status = $hasStock ? 'In Stock' : 'Out of Stock';

            } else {
                $finalPrice   = calculateProductPrice($originalPrice, $salePrice);
                $stock_status = $product->quantity > 0 ? 'In Stock' : 'Out of Stock';
            }

            $wishlist_arr['id']                       = $value->id;
            $wishlist_arr['product_id']               = $product->id;
            $wishlist_arr['title']                    = $product->title;
            $wishlist_arr['slug']                     = $product->slug;
            $wishlist_arr['image']                    = resolve_product_image($product->image);
            $wishlist_arr['product_type']             = $product->product_type;
            $wishlist_arr['originalPrice']            = $originalPrice;
            $wishlist_arr['salePrice']                = $salePrice;
            $wishlist_arr['finalPrice']               = $finalPrice;
            $wishlist_arr['stock_status']             = $stock_status;
            $wishlist_arr['attribute_title']          = $attribute_title;
            $wishlist_arr['sub_attribute_title']      = $sub_attribute_title;
            $wishlist_arr['product_variant_name']     = $product_variant_name;
            $wishlist_arr['product_sub_variant_name'] = $product_sub_variant_name;

            $wishlist_arrs[] = $wishlist_arr;
        }

        return [
            'wishlist_arrs' => $wishlist_arrs,
            'search'        => $search,
            'get_wishlists' => $get_wishlists,

        ];
    }

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

    public function remove_wishlist($id)
    {
        $id  = checkDecrypt($id);
        $customerId = Auth::guard('customers')->id();

        Wishlist::where('id', $id)
                ->where('customer_id', $customerId)
                ->delete();

        $wishlist_count = Wishlist::where('customer_id', $customerId)->count();

        $status  = 'success';
        $message = translate('Removed from wishlist');
        return back()->withErrors([$status => $message]);

        /*return response()->json([
            'error'          => false,
            'message'        => 'Removed from wishlist',
            'wishlist_count' => $wishlist_count,
        ]);*/
    }

}
