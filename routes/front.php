<?php 

use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\LoginController;
use App\Http\Controllers\Front\DashboardController;
use App\Http\Controllers\Front\MyAddressController;
use App\Http\Controllers\Front\MyWishlistController;
use App\Http\Controllers\Front\MyOrderController;
use App\Http\Controllers\Front\ProductController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;


use Illuminate\Support\Facades\Route;

Route::get('/config-all', function () {
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    //Artisan::call('optimize:clear');
    return 'Config cache cleared';
});

Route::middleware(['user_checkLogin'])->group(function () {
	Route::match(['get', 'post'], '/user/login', [LoginController::class, 'index'])->name('user.login');
});


Route::match(['get', 'post'], '/', [HomeController::class, 'index'])->name('home');
Route::match(['get', 'post'], '/aboutus', [HomeController::class, 'aboutus'])->name('aboutus');
Route::match(['get', 'post'], '/contactus', [HomeController::class, 'contactus'])->name('contactus');
Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/shipping-policy', [HomeController::class, 'shippingPolicy'])->name('shipping-policy');
Route::get('/terms-conditions', [HomeController::class, 'termsConditions'])->name('terms-conditions');
Route::get('/returns-policy', [HomeController::class, 'returnsPolicy'])->name('returns-policy');
Route::get('/disclaimer', [HomeController::class, 'disclaimer'])->name('disclaimer');
Route::match(['get', 'post'], '/shop', [ProductController::class, 'index'])->name('shop');
Route::match(['get', 'post'], '/product-category/{slug}', [ProductController::class, 'mid_level_category'])->name('mid-level-category');
Route::match(['get', 'post'], '/product-category/{parent_slug}/{child_slug}', [ProductController::class, 'bottom_level_category'])->name('bottom-level-category');
Route::match(['get', 'post'], '/product-category/{parent_slug}/{child_slug}/{sub_child_slug}/{fourth_slug}', [ProductController::class, 'products'])->name('fourth-level-products');
Route::match(['get', 'post'], '/product-category/{parent_slug}/{child_slug}/{sub_child_slug}', [ProductController::class, 'products'])->name('products');
Route::match(['get', 'post'], '/product_filter', [ProductController::class, 'product_filter'])->name('product-filter');
Route::get('/search-suggestions', [ProductController::class, 'search_suggestions'])->name('search.suggestions');
Route::match(['get', 'post'], '/search', [ProductController::class, 'search'])->name('search');
Route::match(['get', 'post'], '/product/{slug}', [ProductController::class, 'product_detail'])->name('product-detail');
Route::match(['get', 'post'], '/variable-product-price', [ProductController::class, 'variable_product_price'])->name('variable-product-price');



Route::prefix('user')->name('front.')->group(function () {

	Route::match(['get', 'post'], '/forgot-password', [LoginController::class, 'forgot_password'])->name('forgot-password');
	Route::match(['get', 'post'], '/reset-password/{user_id}', [LoginController::class, 'reset_password'])->name('reset-password');
	Route::match(['get', 'post'], '/signup', [LoginController::class, 'signup'])->name('signup');
	Route::match(['get', 'post'], '/customer-signup', [LoginController::class, 'customer_signup'])->name('customer_signup');
	Route::match(['get', 'post'], '/trade-signup', [LoginController::class, 'trade_signup'])->name('trade_signup');

	// Get States by CountryID
    Route::match(['get', 'post'], 'product/get_state_by_country_id', [LoginController::class, 'get_state_by_country_id'])->name('get_state_by_country_id');
    // Get Cities by CountryID & StateID
    Route::match(['get', 'post'], 'product/get_city_by_state_id', [LoginController::class, 'get_city_by_state_id'])->name('get_city_by_state_id');
});

/*Route::group(['middleware' => 'frontuserAuth', 'prefix' => 'user' ], function () {
	
});*/
 

Route::middleware('frontuserAuth')->group(function () {

    // USER PANEL ROUTES
    Route::prefix('user')->name('user.')->group(function () {
        Route::match(['get', 'post'], '/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::match(['get', 'post'], '/logout', [DashboardController::class, 'logout'])->name('logout');
        Route::match(['get', 'post'], '/profile', [DashboardController::class, 'profile_update'])->name('profile');
        Route::match(['get', 'post'], '/order', [MyOrderController::class, 'index'])->name('order');
        Route::get('/order-detail/{order_id}', [MyOrderController::class, 'orderDetail'])->name('order-detail');
        Route::get('/invoice/{order_id}', [MyOrderController::class, 'invoice'])->name('order-invoice');
        Route::match(['get', 'post'], '/address', [MyAddressController::class, 'index'])->name('address');
        Route::match(['get', 'post'], '/wishlist', [MyWishlistController::class, 'index'])->name('wishlist');
    });

    // CART ROUTE
    Route::match(['get', 'post'], '/cart', [CartController::class, 'index'])->name('cart');
    Route::match(['get', 'post'], '/add-to-cart', [CartController::class, 'add_to_cart'])->name('add-to-cart');
    Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');
    Route::post('/cart/delete', [CartController::class, 'deleteCart'])->name('cart.delete');
    Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::get('/cart/reload', [CartController::class, 'reloadCart'])->name('cart.reload');
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');
    Route::post('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.remove-coupon');

    // Checkout Routes
    Route::post('/create-payment-intent', [CheckoutController::class, 'createPaymentIntent'])->name('create.payment.intent');
    Route::match(['get', 'post'], '/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::post('/apply-coupon', [CheckoutController::class, 'applyCoupon'])->name('apply-coupon');
    Route::post('/remove-coupon', [CheckoutController::class, 'removeCoupon'])->name('remove-coupon');
    Route::post('/select-shipping', [CheckoutController::class, 'selectShipping'])->name('checkout.select-shipping');
    Route::match(['get', 'post'], '/save-address', [CheckoutController::class, 'save_address'])->name('save-address');
    Route::match(['get', 'post'], '/place_order', [CheckoutController::class, 'place_order'])->name('place_order');
    Route::match(['get', 'post'], '/order_products', [CheckoutController::class, 'order_products'])->name('order_products');

    // Wishlist Add & Remove
    Route::match(['get', 'post'], '/add-to-wishlist', [MyWishlistController::class, 'add_to_wishlist'])->name('add-to-wishlist');
    Route::match(['get', 'post'], '/remove-wishlist/{id}', [MyWishlistController::class, 'remove_wishlist'])->name('remove-wishlist');

    // 
    Route::post('/get-address-detail',  [MyAddressController::class, 'get_address_detail'])->name('get-address-detail');
    Route::post('/address/update',      [MyAddressController::class, 'update_address'])->name('update-address');
    Route::post('/address/delete',      [MyAddressController::class, 'delete_address'])->name('delete-address');
    Route::post('/address/set-default', [MyAddressController::class, 'set_default_address'])->name('set-default-address');

});
