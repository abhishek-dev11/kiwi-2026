<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SuppliersController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\ShippingSettingController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\XeroController;
use App\Http\Controllers\Admin\PagesController;
use App\Http\Controllers\Admin\ContactEnquiryController;
use Illuminate\Support\Facades\Route;


Route::get('/admin/config-all', function () {
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    //Artisan::call('optimize:clear');
    return 'Config cache cleared 2';
});


Route::middleware(['admin_checkLogin'])->group(function () {
    Route::match(['get', 'post'], '/admin', [LoginController::class, 'index'])->name('admin.login');
    // Route::match(['get', 'post'], '/forgot-password', [LoginController::class, 'forgot_password'])->name('admin.forgot_password');
    // Route::match(['get', 'post'], '/reset-password/{user_id}', [LoginController::class, 'reset_password'])->name('admin.reset_password');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::match(['get', 'post'], '/forgot-password', [LoginController::class, 'forgot_password'])
        ->name('forgot_password');
    Route::match(['get', 'post'], '/reset-password/{user_id}', [LoginController::class, 'reset_password'])->name('reset_password');
});



Route::group(['prefix' => 'xero'], function () {
    Route::get('/connect',    [XeroController::class, 'connect']);
    Route::get('/callback',   [XeroController::class, 'callback']);
    Route::get('/status',     [XeroController::class, 'status']);
    Route::delete('/disconnect', [XeroController::class, 'disconnect']);
});

// Protect these with admin middleware — not for public access
Route::group(['middleware' => 'adminAuth', 'prefix' => 'admin'], function () {

    
    Route::match(['get', 'post'], '/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/dashboard/orders-chart', [DashboardController::class, 'ordersChartData'])->name('admin.dashboard.orders_chart');
    Route::match(['get', 'post'], '/logout', [DashboardController::class, 'logout'])->name('admin.logout');
    Route::match(['get', 'post'], '/profile', [DashboardController::class, 'user_profile'])->name('admin.profile');
    Route::match(['get', 'post'], '/get_append_view', [DashboardController::class, 'get_append_view'])->name('admin.get_append_view');

    Route::match(['get', 'post'],'topcategories', [CategoryController::class, 'topindex'])->name('admin.topcategories');
    Route::match(['get', 'post'], 'topcategory/add', [CategoryController::class, 'topstore'])->name('admin.topcategories.add');
    Route::get('topcategory/edit/{id}', [CategoryController::class, 'topstore'])->name('admin.topcategories.edit');
    Route::get('topcategory/delete/{id}', [CategoryController::class, 'topdelete'])->name('admin.topcategories.delete');

    Route::match(['get', 'post'],'midcategories', [CategoryController::class, 'midindex'])->name('admin.midcategories');
    Route::match(['get', 'post'], 'midcategory/add', [CategoryController::class, 'midstore'])->name('admin.midcategories.add');
    Route::get('midcategory/edit/{id}', [CategoryController::class, 'midstore'])->name('admin.midcategories.edit');
    Route::get('midcategory/delete/{id}', [CategoryController::class, 'middelete'])->name('admin.midcategories.delete');

    Route::match(['get', 'post'],'bottomcategories', [CategoryController::class, 'bottomindex'])->name('admin.bottomcategories');
    Route::match(['get', 'post'], 'bottomcategory/add', [CategoryController::class, 'bottomstore'])->name('admin.bottomcategories.add');
    Route::get('bottomcategory/edit/{id}', [CategoryController::class, 'bottomstore'])->name('admin.bottomcategories.edit');
    Route::get('bottomcategory/delete/{id}', [CategoryController::class, 'bottomdelete'])->name('admin.bottomcategories.delete');

    Route::match(['get', 'post'],'subbottomcategories', [CategoryController::class, 'subbottomindex'])->name('admin.subbottomcategories');
    Route::match(['get', 'post'], 'subbottomcategory/add', [CategoryController::class, 'subbottomstore'])->name('admin.subbottomcategories.add');
    Route::get('subbottomcategory/edit/{id}', [CategoryController::class, 'subbottomstore'])->name('admin.subbottomcategories.edit');
    Route::get('subbottomcategory/delete/{id}', [CategoryController::class, 'subbottomdelete'])->name('admin.subbottomcategories.delete');

    Route::match(['get', 'post'], 'get_mid_level_category_id', [CategoryController::class, 'get_mid_level_category_id'])->name('admin.categories.get_mid_level_category_id');

    // Products
    Route::match(['get', 'post'],'products', [ProductController::class, 'index'])->name('admin.products');
    Route::match(['get', 'post'], 'product/add', [ProductController::class, 'store'])->name('admin.products.add');
    Route::get('product/edit/{id}', [ProductController::class, 'store'])->name('admin.products.edit');
    Route::get('product/delete/{id}', [ProductController::class, 'delete'])->name('admin.products.delete');
    Route::match(['get', 'post'],'product/delete_seleted/', [ProductController::class, 'delete_seleted'])->name('admin.products.delete_seleted');
    Route::match(['get', 'post'], 'product/get_child_category', [ProductController::class, 'get_child_category'])->name('admin.products.get_child_category');

    // Products Import
    Route::match(['get', 'post'],'products-import', [ProductController::class, 'products_import'])->name('admin.productsimport');
    // Route::match(['get', 'post'],'import-progress/{id}', [ProductController::class, 'getProgress']);

     Route::match(['get','post'], 'productsimport', [ProductController::class, 'products_import'])
        ->name('admin.productsimport');

    // ✅ Progress routes
    Route::get('import-progress/{importId}', [ProductController::class, 'import_progress'])->name('admin.import.progress');
    Route::post('import-cancel/{importId}',  [ProductController::class, 'import_cancel'])->name('admin.import.cancel');

    // Aussie Pacific API sync
    Route::post('aussiepacific-import', [ProductController::class, 'aussiepacific_import'])->name('admin.aussiepacific.import');

    // Banbury API sync
    Route::post('banbury-import', [ProductController::class, 'banbury_import'])->name('admin.banbury.import');

    // Logo Line API sync
    Route::post('logoline-import', [ProductController::class, 'logoline_import'])->name('admin.logoline.import');

    // Fashion Biz (Biz Collection) API sync
    Route::post('fashionbiz-import', [ProductController::class, 'fashionbiz_import'])->name('admin.fashionbiz.import');

    // Trends API sync
    Route::post('trends-import', [ProductController::class, 'trends_import'])->name('admin.trends.import');

    // Premium Apparel API sync
    Route::post('premiumapparel-import', [ProductController::class, 'premiumapparel_import'])->name('admin.premiumapparel.import');

    // Attributes
    Route::match(['get', 'post'],'attributes', [AttributeController::class, 'index'])->name('admin.attributes');
    Route::match(['get', 'post'], 'attribute/add', [AttributeController::class, 'store'])->name('admin.attribute.add');
    Route::get('attribute/edit/{id}', [AttributeController::class, 'store'])->name('admin.attribute.edit');
    Route::get('attribute/delete/{id}', [AttributeController::class, 'delete'])->name('admin.attribute.delete');

    // Customer
    Route::match(['get', 'post'],'customers', [CustomerController::class, 'index'])->name('admin.customers');
    Route::match(['get', 'post'], 'customer/add', [CustomerController::class, 'store'])->name('admin.customer.add');
    Route::get('customer/edit/{id}', [CustomerController::class, 'store'])->name('admin.customer.edit');

    // Trade Customer
    Route::match(['get', 'post'],'trade-customers', [CustomerController::class, 'trade_list'])->name('admin.tradecustomers');
    Route::match(['get', 'post'], 'trade-customer/add', [CustomerController::class, 'trade_store'])->name('admin.tradecustomer.add');
    Route::get('trade-customer/edit/{id}', [CustomerController::class, 'trade_store'])->name('admin.tradecustomer.edit');

    // Delete Both Customer Or Trade Customer
    Route::get('customer/delete/{id}', [CustomerController::class, 'delete'])->name('admin.customer.delete');

    // Customer Group
    Route::match(['get', 'post'],'customer-groups', [CustomerGroupController::class, 'index'])->name('admin.customergroups');
    Route::match(['get', 'post'], 'customer-group/add', [CustomerGroupController::class, 'store'])->name('admin.customergroup.add');
    Route::get('customer-group/edit/{id}', [CustomerGroupController::class, 'store'])->name('admin.customergroup.edit');
    Route::get('customer-group/delete/{id}', [CustomerGroupController::class, 'delete'])->name('admin.customergroup.delete');


    // Get States by CountryID
    Route::match(['get', 'post'], 'product/get_state_by_country_id', [CustomerController::class, 'get_state_by_country_id'])->name('admin.get_state_by_country_id');
    // Get Cities by CountryID & StateID
    Route::match(['get', 'post'], 'product/get_city_by_state_id', [CustomerController::class, 'get_city_by_state_id'])->name('admin.get_city_by_state_id');

    // Suppliers
    Route::match(['get', 'post'],'suppliers', [SuppliersController::class, 'index'])->name('admin.suppliers');
    Route::match(['get', 'post'], 'supplier/add', [SuppliersController::class, 'store'])->name('admin.supplier.add');
    Route::get('supplier/edit/{id}', [SuppliersController::class, 'store'])->name('admin.supplier.edit');
    Route::get('supplier/delete/{id}', [SuppliersController::class, 'delete'])->name('admin.supplier.delete');

    // Coupons
    Route::match(['get', 'post'], 'coupons', [CouponController::class, 'index'])->name('admin.coupons');
    Route::match(['get', 'post'], 'coupon/add', [CouponController::class, 'store'])->name('admin.coupon.add');
    Route::get('coupon/edit/{id}', [CouponController::class, 'store'])->name('admin.coupon.edit');
    Route::get('coupon/delete/{id}', [CouponController::class, 'delete'])->name('admin.coupon.delete');

    // Shipping Methods
    Route::match(['get', 'post'], 'shipping-methods', [ShippingMethodController::class, 'index'])->name('admin.shipping-methods');
    Route::match(['get', 'post'], 'shipping-method/add', [ShippingMethodController::class, 'store'])->name('admin.shipping-method.add');
    Route::get('shipping-method/edit/{id}', [ShippingMethodController::class, 'store'])->name('admin.shipping-method.edit');
    Route::get('shipping-method/delete/{id}', [ShippingMethodController::class, 'delete'])->name('admin.shipping-method.delete');

    // Shipping Settings
    Route::match(['get', 'post'], 'shipping-settings', [ShippingSettingController::class, 'edit'])->name('admin.shipping-settings');

    // Order
    Route::match(['get', 'post'],'orders', [OrderController::class, 'index'])->name('admin.order');
    Route::match(['get', 'post'], 'order/details/{id}', [OrderController::class, 'details'])->name('admin.order.details');
    Route::match(['get', 'post'], 'order/invoice/{id}', [OrderController::class, 'invoice'])->name('admin.order.invoice');
    Route::match(['get', 'post'], 'order/update_order_status/{id}', [OrderController::class, 'updateOrderStatus'])->name('admin.order.order_status');

    // Pages
    Route::get('pages', [PagesController::class, 'index'])->name('admin.pages');
    Route::match(['get', 'post'],'page/edit/{id}', [PagesController::class, 'add_pages'])->name('admin.page.edit');

    // Contact Enquiries
    Route::match(['get', 'post'],'contact-enquiries', [ContactEnquiryController::class, 'index'])->name('admin.contact-enquiries');
    Route::get('contact-enquiry/view/{id}', [ContactEnquiryController::class, 'view'])->name('admin.contact-enquiry.view');
    Route::get('contact-enquiry/delete/{id}', [ContactEnquiryController::class, 'delete'])->name('admin.contact-enquiry.delete');
});

/*Route::get('/admin-test', function () {
    $middleware = new \App\Http\Middleware\AdminCheckLogin();
    return $middleware->handle(request(), function () { return 'Middleware works!'; });
});*/
