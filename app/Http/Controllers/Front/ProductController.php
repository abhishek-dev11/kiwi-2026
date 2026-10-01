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

class ProductController extends Controller
{

    /* Top Level Categories */
    public function index(Request $request)
    {
        $common = array();
        $common['title'] = 'Top Categories';
        $common['main_menu']    = 'categories';
        $common['submain_menu'] = 'topcategory';

        $common['filter_front_topcategory_title']     = "";
        $common['filter_front_topcategory_status'] = "";

        $filterFields = ['filter_front_topcategory_title', 'filter_front_topcategory_status'];
        $common =  updateSessionFilters($request,$filterFields,$common);

        $categories = Categories::whereNull('is_delete')->whereNull('top_level_category_id')->whereNull('mid_level_category_id')->orderBy('title', 'asc');

        if($common['filter_front_topcategory_title']){
            $categories->where('title','like','%'.$common['filter_front_topcategory_title'].'%');         
        }

        if($common['filter_front_topcategory_status']){
            $categories->where('status',$common['filter_front_topcategory_status']);
        }

        //$categories  = $categories->paginate(config('adminconfig.records_per_page'));
        $categories  = $categories->get();

        $categories_list = array();
        foreach ($categories as $key => $value) {
            $childCount = Categories::whereNull('is_delete')
                ->where('top_level_category_id', $value->id)
                ->whereNull('mid_level_category_id')
                ->count();

            $categories_arr = [];
            $categories_arr['id'] = $value->id;
            $categories_arr['title'] = $value->title;
            $categories_arr['slug'] = $value->slug;
            $categories_arr['image'] = $value->image;
            $categories_arr['count'] = $childCount > 0
                ? $childCount
                : Products::where(ConditionQuery())
                    ->where(function ($query) use ($value) {
                        $query->where('category', $value->id)
                            ->orWhere('top_level_category_id', $value->id);
                    })
                    ->count();
            $categories_list[] = $categories_arr;
        }
        return view('front.shop.shop', compact('common', 'categories','categories_list'));
    }

    /* Mid Level Categories */
    public function mid_level_category(Request $request, $slug)
    {
        $breadcrumbs_arrs = custome_Breadcrumbs($slug);

        // Fetch top category and all needed data in one go
        $get_top_category = $this->getCategoryBySlug($slug);
        $get_category     = $get_top_category; // same slug lookup — reuse it

        // Build mid-level categories query
        $categories = Categories::whereNull('is_delete')
            ->whereNotNull('top_level_category_id')
            ->whereNull('mid_level_category_id')
            ->orderBy('title');

        if ($get_top_category) {
            $categories->where('top_level_category_id', $get_top_category->id);
        }
        //$categories = $categories->paginate(config('adminconfig.records_per_page'));
        $categories = $categories->get();
        $show_products = $categories->count() === 0;
        $get_products = $show_products && $get_category
            ? $this->productsForCategory($get_category)
            : collect();

        // Collect all category IDs to batch-count sub-categories
        $categoryIds = $categories->pluck('id');
        $countMap    = Categories::whereIn('mid_level_category_id', $categoryIds)
            ->whereNull('bottom_level_category_id')
            ->selectRaw('mid_level_category_id, COUNT(*) as total')
            ->groupBy('mid_level_category_id')
            ->pluck('total', 'mid_level_category_id');

        $categories_list = $categories->map(fn($cat) => [
            'id'          => $cat->id,
            'title'       => $cat->title,
            'parent_slug' => $slug,
            'child_slug'  => $cat->slug,
            'image'       => $cat->image,
            'count'       => $countMap->get($cat->id, 0) ?: $this->productCountForCategory($cat),
        ])->values()->all();

        // Fetch top-level active categories
        $top_categories = Categories::whereNull('is_delete')
            ->where('status', 'Active')
            ->whereNull('top_level_category_id')
            ->get(['id', 'title', 'slug']);

        $wishlistedIds = $this->wishlistedProductIds();

        return view('front.shop.mid-level-category', compact(
            'categories', 'categories_list', 'top_categories',
            'breadcrumbs_arrs', 'get_category', 'show_products',
            'get_products', 'wishlistedIds'
        ));
    }

    /* Bottom Level Categories */
    public function bottom_level_category(Request $request, $parent_slug, $child_slug)
    {
        $breadcrumbs_arrs = custome_Breadcrumbs($parent_slug, $child_slug);

        // Fetch required categories upfront — avoid repeated slug queries
        $get_middle_category = $this->getCategoryBySlug($child_slug);
        $get_top_category    = $this->getCategoryBySlug($parent_slug, withDeleted: false);
        $get_category        = $get_middle_category; // same as child_slug lookup — reuse

        // Build bottom-level categories query
        $categories = Categories::whereNull('is_delete')
            ->whereNotNull('top_level_category_id')
            ->whereNotNull('mid_level_category_id')
            ->whereNull('bottom_level_category_id')
            ->orderBy('title');

        if ($get_middle_category) {
            $categories->where('mid_level_category_id', $get_middle_category->id);
        }

        //$categories = $categories->paginate(config('adminconfig.records_per_page'));
        $categories = $categories->get();

        // Batch-fetch sub-category counts to avoid N+1
        $categoryIds    = $categories->pluck('id');
        $subCountMap    = Categories::whereIn('bottom_level_category_id', $categoryIds)
            ->selectRaw('bottom_level_category_id, COUNT(*) as total')
            ->groupBy('bottom_level_category_id')
            ->pluck('total', 'bottom_level_category_id');

        $categories_list = $categories->map(fn($cat) => [
            'id'             => $cat->id,
            'title'          => $cat->title,
            'parent_slug'    => $parent_slug,
            'child_slug'     => $child_slug,
            'sub_child_slug' => $cat->slug,
            'image'          => $cat->image,
            'count'          => $subCountMap->get($cat->id, 0) ?: $this->productCountForCategory($cat),
        ])->values()->all();

        $show_products = $categories->count() === 0;
        $get_products = $show_products && $get_category
            ? $this->productsForCategory($get_category)
            : collect();

        // Build top-level sibling categories list
        $top_categories = Categories::whereNull('is_delete')
            ->whereNotNull('top_level_category_id')
            ->whereNull('mid_level_category_id')
            ->when($get_top_category, fn($q) => $q->where('top_level_category_id', $get_top_category->id))
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $top_categories_list = $top_categories->map(fn($cat) => [
            'id'          => $cat->id,
            'title'       => $cat->title,
            'parent_slug' => $parent_slug,
            'child_slug'  => $cat->slug,
        ])->values()->all();

        $wishlistedIds = $this->wishlistedProductIds();

        return view('front.shop.bottom-level-category', compact(
            'categories', 'categories_list', 'get_top_category',
            'top_categories_list', 'get_products', 'get_category',
            'breadcrumbs_arrs', 'parent_slug', 'show_products',
            'wishlistedIds'
        ));
    }

    // Shared helper — avoids repeating the same slug query pattern
    private function getCategoryBySlug(string $slug, bool $withDeleted = true): ?Categories
    {
        $query = Categories::where('slug', $slug);
        if (!$withDeleted) {
            $query->whereNull('is_delete');
        }
        return $query->first();
    }

    private function productsForCategory(Categories $category)
    {
        return Products::where(ConditionQuery())
            ->where(function ($query) use ($category) {
                $query->where('category', $category->id);

                if (is_null($category->top_level_category_id) && is_null($category->mid_level_category_id)) {
                    $query->orWhere('top_level_category_id', $category->id);
                } elseif (is_null($category->mid_level_category_id)) {
                    $query->orWhere('mid_level_category_id', $category->id);
                } elseif (is_null($category->bottom_level_category_id)) {
                    $query->orWhere('bottom_level_category_id', $category->id);
                }
            })
            ->with(['subVariantItems' => function ($query) {
                $query->where('status', 'active')
                    ->where('is_deleted', 0)
                    ->orderBy('price', 'asc');
            }])
            ->orderBy('id', 'desc')
            ->paginate(config('adminconfig.records_per_page'));
    }

    private function productCountForCategory(Categories $category): int
    {
        return Products::where(ConditionQuery())
            ->where(function ($query) use ($category) {
                $query->where('category', $category->id);

                if (is_null($category->top_level_category_id) && is_null($category->mid_level_category_id)) {
                    $query->orWhere('top_level_category_id', $category->id);
                } elseif (is_null($category->mid_level_category_id)) {
                    $query->orWhere('mid_level_category_id', $category->id);
                } elseif (is_null($category->bottom_level_category_id)) {
                    $query->orWhere('bottom_level_category_id', $category->id);
                }
            })
            ->distinct('id')
            ->count('id');
    }

    private function wishlistedProductIds(): array
    {
        if (!Auth::guard('customers')->check()) {
            return [];
        }

        return Wishlist::where('customer_id', Auth::guard('customers')->id())
            ->pluck('product_id')
            ->toArray();
    }


    public function products(Request $request, $parent_slug, $child_slug, $sub_child_slug, $fourth_slug = null)
    {
        // Fetch all needed categories in minimal queries
        [$get_top_category, $get_mid_category, $get_bottom_category] = $this->fetchCategoryBySlug(
            $parent_slug, $child_slug, $sub_child_slug
        );
        $get_fourth_category = $fourth_slug ? Categories::whereNull('is_delete')->where('slug', $fourth_slug)->first() : null;
        $selected_category = $get_fourth_category ?: $get_bottom_category;
        $show_products = true;

        // Single reusable query scope for mid-level categories under a top category
        $midLevelQuery = Categories::whereNull('is_delete')
            ->whereNotNull('top_level_category_id')
            ->whereNull('mid_level_category_id')
            ->orderBy('title');

        if ($get_top_category) {
            $midLevelQuery->where('top_level_category_id', $get_top_category->id);
        }

        // Fetch once, use for both lists (avoid duplicate query)
        $midLevelCategories = $midLevelQuery->get(['id', 'title', 'slug']);

        // Build top_categories_list (child_slug uses each category's own slug)
        $top_categories_list = $midLevelCategories->map(fn($cat) => [
            'id'             => $cat->id,
            'title'          => $cat->title,
            'parent_slug'    => $parent_slug,
            'child_slug'     => $cat->slug,
            'sub_child_slug' => $sub_child_slug,
        ])->values()->all();

        $categories = collect();
        $categories_list = [];

        if (!$fourth_slug && $get_bottom_category) {
            $categories = Categories::whereNull('is_delete')
                ->where('bottom_level_category_id', $get_bottom_category->id)
                ->orderBy('title')
                ->paginate(config('adminconfig.records_per_page'));

            $show_products = $categories->count() === 0;

            $categories_list = $categories->map(fn($cat) => [
                'id'             => $cat->id,
                'title'          => $cat->title,
                'parent_slug'    => $parent_slug,
                'child_slug'     => $child_slug,
                'sub_child_slug' => $sub_child_slug,
                'fourth_slug'    => $cat->slug,
                'image'          => $cat->image,
                'count'          => $this->productCountForCategory($cat),
            ])->values()->all();
        }

        // Fetch products only if the selected category is a final category.
        $get_products = $show_products && $selected_category
            ? $this->productsForCategory($selected_category)
            : collect();

        $breadcrumbs_arrs = custome_Breadcrumbs($parent_slug, $child_slug, $sub_child_slug, $fourth_slug);

        return view('front.shop.products', compact(
            'breadcrumbs_arrs',
            'categories',
            'categories_list',
            'get_products',
            'get_bottom_category',
            'get_fourth_category',
            'get_top_category',
            'top_categories_list',
            'get_mid_category',
            'show_products'
        ));
    }

    // Extract repeated slug lookups into a helper
    private function fetchCategoryBySlug($parent_slug, $child_slug, $sub_child_slug): array
    {
        return [
            Categories::whereNull('is_delete')->where('slug', $parent_slug)->first(),
            Categories::whereNull('is_delete')->where('slug', $child_slug)->first(),
            Categories::whereNull('is_delete')->where('slug', $sub_child_slug)->first(),
        ];
    }
    

    public function product_filter(Request $request)
    {
        if ($request->isMethod('post')) {
            $products = Products::where(ConditionQuery())->orderBy('id', 'desc')->where(ConditionQuery());
            if($request->search_product){
                $products->where('title','like','%'.$request->search_product.'%');         
            }

            if($request->top_category_id){
                $products->where('top_level_category_id',$request->top_category_id);
            }

            if($request->mid_category_id){
                $products->where('mid_level_category_id',$request->mid_category_id);
            }

            if($request->bottom_category_id){
                $selectedCategory = Categories::where(ConditionQuery())
                    ->where('id', $request->bottom_category_id)
                    ->first();

                if ($selectedCategory) {
                    $products->where(function ($query) use ($selectedCategory) {
                        $query->where('category', $selectedCategory->id);

                        if (is_null($selectedCategory->bottom_level_category_id)) {
                            $query->orWhere('bottom_level_category_id', $selectedCategory->id);
                        }
                    });
                } else {
                    $products->where('category', $request->bottom_category_id);
                }
            }


            // BUSINESS DISCOUNT
            $discountPercentage = 0;
            if (Auth::guard('customers')->check()) {
                $customer = Auth::guard('customers')->user();
                if ($customer->customer_type == 'business') {
                    $CustomerGroups = CustomerGroups::select('discount_percentage')
                        ->where(ConditionQuery())
                        ->where('id', $customer->customer_group_id)
                        ->first();
                    $discountPercentage = $CustomerGroups->discount_percentage ?? 0;
                }
            }

            // PRICE FILTER
            /*if ($request->min_price != '' && $request->max_price != '') {
                if ($discountPercentage > 0) {
                    // APPLY BUSINESS DISCOUNT FILTER
                    $products->whereRaw("
                        (
                            CASE
                                WHEN discount_price IS NOT NULL AND discount_price > 0
                                THEN discount_price - (discount_price * ? / 100)
                                ELSE regular_price - (regular_price * ? / 100)
                            END
                        ) BETWEEN ? AND ?
                    ", [
                        $discountPercentage,
                        $discountPercentage,
                        $request->min_price,
                        $request->max_price
                    ]);
                } else {
                    // NORMAL USER FILTER
                    $products->whereBetween('regular_price', [
                        $request->min_price,
                        $request->max_price
                    ]);
                }
            }*/

            // PRICE FILTER
            if ($request->min_price != '' && $request->max_price != '') {
                if ($discountPercentage > 0) {
                    // APPLY BUSINESS DISCOUNT FILTER
                    $products->where(function($query) use ($request, $discountPercentage) {
                        // Simple products - apply discount to regular/discount price
                        $query->where(function($q) use ($request, $discountPercentage) {
                            $q->where('product_type', 'simple')
                              ->whereRaw("
                                  (
                                      CASE
                                          WHEN discount_price IS NOT NULL AND discount_price > 0
                                          THEN discount_price - (discount_price * ? / 100)
                                          ELSE regular_price - (regular_price * ? / 100)
                                      END
                                  ) BETWEEN ? AND ?
                              ", [
                                  $discountPercentage,
                                  $discountPercentage,
                                  $request->min_price,
                                  $request->max_price
                              ]);
                        })
                        // Variable products - check min variant price after discount
                        ->orWhere(function($q) use ($request, $discountPercentage) {
                            $q->where('product_type', 'variable')
                              ->whereHas('subVariantItems', function($subQ) use ($request, $discountPercentage) {
                                  $subQ->whereRaw("
                                      (price - (price * ? / 100)) BETWEEN ? AND ?
                                  ", [
                                      $discountPercentage,
                                      $request->min_price,
                                      $request->max_price
                                  ]);
                              });
                        });
                    });
                } else {
                    // NORMAL USER FILTER
                    $products->where(function($query) use ($request) {
                        // Simple products
                        $query->where(function($q) use ($request) {
                            $q->where('product_type', 'simple')
                              ->whereBetween('regular_price', [
                                  $request->min_price,
                                  $request->max_price
                              ]);
                        })
                        // Variable products - get min variant price
                        ->orWhere(function($q) use ($request) {
                            $q->where('product_type', 'variable')
                              ->whereHas('subVariantItems', function($subQ) use ($request) {
                                  $subQ->whereBetween('price', [
                                      $request->min_price,
                                      $request->max_price
                                  ]);
                              });
                        });
                    });
                }
            }
             

            // STOCK STATUS FILTER
            /*if($request->stock_status == 'in_stock'){
                $products->where('quantity', '>', 0);
            } elseif($request->stock_status == 'on_sale'){
                $products->whereNotNull('discount_price');
            } elseif($request->stock_status == 'all_product'){
                // NO FILTER
            }*/

            // STOCK STATUS FILTER
            if ($request->stock_status == 'in_stock') {
                $products->where(function($query) {
                    // Simple products - check quantity
                    $query->where(function($q) {
                        $q->where('product_type', 'simple')
                          ->where('quantity', '>', 0);
                    })
                    // Variable products - check if any active variant has stock > 0
                    ->orWhere(function($q) {
                        $q->where('product_type', 'variable')
                          ->whereHas('subVariantItems', function($subQ) {
                              $subQ->where('stock', '>', 0)
                                   ->where('status', 'active')
                                   ->where('is_deleted', 0);
                          });
                    });
                });

            } elseif ($request->stock_status == 'on_sale') {
                $products->where(function($query) {
                    // Simple products - check discount_price
                    $query->where(function($q) {
                        $q->where('product_type', 'simple')
                          ->whereNotNull('discount_price')
                          ->where('discount_price', '>', 0);
                    })
                    // Variable products - no sale price in variants table
                    // so check product's discount_price itself
                    ->orWhere(function($q) {
                        $q->where('product_type', 'variable')
                          ->whereNotNull('discount_price')
                          ->where('discount_price', '>', 0);
                    });
                });

            } elseif ($request->stock_status == 'all_product') {
                // NO FILTER
            }

            // Total products count
            $total_products = (clone $products)->count();

            $get_products = $products->with(['subVariantItems' => function($q) {
                $q->where('status', 'active')
                  ->where('is_deleted', 0)
                  ->orderBy('price', 'asc');
            }])->paginate(15);

            $get_bottom_category = Categories::where([ 'id' => $request->bottom_category_id ])->first();

            $wishlistedIds = [];
            if (Auth::guard('customers')->check()) {
                $wishlistedIds = Wishlist::where('customer_id', Auth::guard('customers')->id())
                                         ->pluck('product_id')
                                         ->toArray();
            }

            //$html = view('front.shop.search-product', compact('get_products', 'get_bottom_category'))->render();
            $html = view('front.shop.search-product', compact(
                'get_products',
                'get_bottom_category',
                'wishlistedIds'
            ))->render();

            return response()->json([
                'error' => false,
                'html'  => $html,
                'count' => $total_products
            ]);   
        }
    }

    /* Global header search — live suggestions dropdown */
    public function search_suggestions(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'error'      => false,
                'products'   => [],
                'categories' => [],
            ]);
        }

        $like = $this->likeTerm($q);

        $products = Products::where(ConditionQuery())
            ->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                      ->orWhere('sku', 'like', $like);
            })
            ->with(['subVariantItems' => function ($query) {
                $query->where('status', 'active')
                      ->where('is_deleted', 0)
                      ->orderBy('price', 'asc');
            }])
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        $products_list = $products->map(function ($product) {
            if ($product->product_type == 'variable') {
                $minVariant = $product->subVariantItems->first();
                $finalPrice = $minVariant ? calculateProductPrice($minVariant->price) : null;
            } else {
                $finalPrice = calculateProductPrice($product->regular_price, $product->discount_price);
            }

            return [
                'title' => $product->title,
                'url'   => route('product-detail', $product->slug),
                'image' => $product->image
                    ? ((str_starts_with($product->image, 'http://') || str_starts_with($product->image, 'https://'))
                        ? $product->image
                        : get_image_upload('Product/' . $product->image))
                    : asset('placeholder/product/image.png'),
                'price' => $finalPrice !== null ? '$' . number_format($finalPrice, 2) : null,
            ];
        })->values();

        $categories = Categories::where(ConditionQuery())
            ->where('title', 'like', $like)
            ->orderBy('title')
            ->limit(5)
            ->get();

        $categories_list = $categories->map(function ($category) {
            return [
                'title' => $this->categoryDisplayTitle($category),
                'url'   => $this->categoryUrl($category),
            ];
        })->values();

        return response()->json([
            'error'      => false,
            'products'   => $products_list,
            'categories' => $categories_list,
        ]);
    }

    /* Global search results page */
    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $products = Products::where(ConditionQuery())
            ->when($q !== '', function ($query) use ($q) {
                $like = $this->likeTerm($q);
                $query->where(function ($sub) use ($like) {
                    $sub->where('title', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhere('short_decripation', 'like', $like);
                });
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->with(['subVariantItems' => function ($query) {
                $query->where('status', 'active')
                      ->where('is_deleted', 0)
                      ->orderBy('price', 'asc');
            }])
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends(['q' => $q]);

        // Batch-resolve a display category title per product (avoid N+1 in the view)
        $categoryIds = collect();
        foreach ($products as $product) {
            $categoryIds->push(
                $product->category,
                $product->bottom_level_category_id,
                $product->mid_level_category_id,
                $product->top_level_category_id
            );
        }
        $categoryTitleMap = Categories::whereIn('id', $categoryIds->filter()->unique())->pluck('title', 'id');

        $productCategoryTitles = [];
        foreach ($products as $product) {
            $catId = $product->category
                ?: ($product->bottom_level_category_id
                    ?: ($product->mid_level_category_id ?: $product->top_level_category_id));
            $productCategoryTitles[$product->id] = $categoryTitleMap->get($catId, '');
        }

        $categories = collect();
        if ($q !== '') {
            $categories = Categories::where(ConditionQuery())
                ->where('title', 'like', $this->likeTerm($q))
                ->orderBy('title')
                ->limit(12)
                ->get()
                ->map(function ($category) {
                    return [
                        'title' => $this->categoryDisplayTitle($category),
                        'image' => $category->image,
                        'url'   => $this->categoryUrl($category),
                    ];
                });
        }

        $wishlistedIds = $this->wishlistedProductIds();

        return view('front.shop.search-results', compact(
            'q', 'products', 'categories', 'productCategoryTitles', 'wishlistedIds'
        ));
    }

    // Escape LIKE wildcards in user input so literal % / _ characters don't behave as wildcards
    private function likeTerm(string $term): string
    {
        return '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
    }

    // Resolve the public URL for a category at any level (top/mid/bottom/fourth)
    private function categoryUrl(Categories $category): string
    {
        if (is_null($category->top_level_category_id) && is_null($category->mid_level_category_id)) {
            return route('mid-level-category', $category->slug);
        }

        if (is_null($category->mid_level_category_id)) {
            $top = Categories::find($category->top_level_category_id);
            return route('bottom-level-category', [
                'parent_slug' => $top->slug ?? '',
                'child_slug'  => $category->slug,
            ]);
        }

        if (is_null($category->bottom_level_category_id)) {
            $top = Categories::find($category->top_level_category_id);
            $mid = Categories::find($category->mid_level_category_id);
            return route('products', [
                'parent_slug'    => $top->slug ?? '',
                'child_slug'     => $mid->slug ?? '',
                'sub_child_slug' => $category->slug,
            ]);
        }

        $top    = Categories::find($category->top_level_category_id);
        $mid    = Categories::find($category->mid_level_category_id);
        $bottom = Categories::find($category->bottom_level_category_id);

        return route('fourth-level-products', [
            'parent_slug'    => $top->slug ?? '',
            'child_slug'     => $mid->slug ?? '',
            'sub_child_slug' => $bottom->slug ?? '',
            'fourth_slug'    => $category->slug,
        ]);
    }

    // Prefix a category's title with its immediate parent's title, so same-named
    // categories under different parents (e.g. "Mens" under Tees vs Jackets) are distinct
    private function categoryDisplayTitle(Categories $category): string
    {
        $parentId = $category->bottom_level_category_id
            ?: ($category->mid_level_category_id ?: $category->top_level_category_id);

        if (!$parentId) {
            return $category->title;
        }

        $parent = Categories::find($parentId);

        return $parent ? $parent->title . ' > ' . $category->title : $category->title;
    }

    public function product_detail(Request $request, $product_slug)
    {
        // PRODUCT
        $product = Products::where(ConditionQuery())
            ->where('slug', $product_slug)
            ->first();

        if (!$product) {
            return redirect('/shop')
                ->with('error', 'Product not found');
        }

        /*
        |--------------------------------------------------------------------------
        | CATEGORY
        |--------------------------------------------------------------------------
        */

        $get_category = Categories::where(ConditionQuery())
            ->where('id', $product->category)
            ->first();

        $category_title  = '';
        $breadcrumbs_arrs = [];

        if ($get_category) {

            $category_title = $get_category->title;

            // CATEGORY SLUGS
            $parent_slug    = '';
            $child_slug     = '';
            $sub_child_slug = $get_category->slug;
            $fourth_slug    = '';

            if ($get_category->bottom_level_category_id) {
                $fourth_slug = $get_category->slug;
                $bottom_category = Categories::select('slug', 'top_level_category_id', 'mid_level_category_id')
                    ->where(ConditionQuery())
                    ->where('id', $get_category->bottom_level_category_id)
                    ->first();
                if ($bottom_category) {
                    $sub_child_slug = $bottom_category->slug;
                    $get_category->top_level_category_id = $bottom_category->top_level_category_id;
                    $get_category->mid_level_category_id = $bottom_category->mid_level_category_id;
                }
            }

            // TOP CATEGORY
            if ($get_category->top_level_category_id) {
                $top_category = Categories::select('slug')
                    ->where(ConditionQuery())
                    ->where('id', $get_category->top_level_category_id)
                    ->first();
                $parent_slug = $top_category->slug ?? '';
            }

            // MID CATEGORY
            if ($get_category->mid_level_category_id) {
                $mid_category = Categories::select('slug')
                    ->where(ConditionQuery())
                    ->where('id', $get_category->mid_level_category_id)
                    ->first();
                $child_slug = $mid_category->slug ?? '';
            }

            // BREADCRUMBS
            $breadcrumbs_arrs = custome_Breadcrumbs(
                $parent_slug,
                $child_slug,
                $sub_child_slug,
                $fourth_slug
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCT GALLERY
        |--------------------------------------------------------------------------
        */

        $gallery_arr = ProductGallery::where('product_id', $product->id)
            ->pluck('image')
            ->map(function ($image) {
                if ($image == '') return asset('placeholder/product/image.png');
                if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) return $image;
                return get_image_upload('ProductGallery/' . $image);
            })
            ->toArray();

        $variantImages = [];
        if ($product->product_type == 'variable') {
            $variantImgRows = ProductVariantImages::where('product_id', $product->id)
                ->get(['product_variant_id', 'imageUrl']);
            foreach ($variantImgRows as $row) {
                $img = $row->imageUrl;
                $url = (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))
                    ? $img
                    : get_image_upload('ProductVariant/' . $img);
                $variantImages[$row->product_variant_id][] = $url;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | STOCK STATUS
        |--------------------------------------------------------------------------
        */

        $is_stock_available = $product->quantity > 0;

        $stock = $is_stock_available ? 'Available in stock' : 'Out of stock';


        $product_variant_arrs = [];
        $product_sub_variant_arrs = [];
        $pro_sub_variant_item_arrs = [];
        $attribute_title = '';
        $sub_attribute_title = '';
        if( $product->product_type == 'variable' ){

            $get_attribute = Attributes::where(ConditionQuery())->where([ 'id' => $product->attribute_id ])->first();
            $attribute_title = $get_attribute->title ?? '';
            
            $product_variant_arrs = ProductVariants::where([ 'product_id' => $product->id, 'attribute_id' => $product->attribute_id ])->get()->toArray();

            /* Sub Variant Attributes */
            $get_sub_attribute = Attributes::where(ConditionQuery())->where([ 'id' => $product->sub_variant_attribute_id ])->first();
            $sub_attribute_title = $get_sub_attribute->title ?? '';

            $product_sub_variant_arrs = ProductSubVariants::where([ 'product_id' => $product->id ])->get()->toArray();
                
            $pro_sub_variant_item_arrs = ProducSubtVariantItems::where([
                'product_id' => $product->id
            ])->get()->toArray();
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUCT DATA
        |--------------------------------------------------------------------------
        */

        $get_product = [
            'id'                        => $product->id,
            'slug'                      => $product->slug,
            'title'                     => $product->title,
            'category_title'            => $category_title,
            'product_image'             => $product->image
                ? ((str_starts_with($product->image, 'http://') || str_starts_with($product->image, 'https://'))
                    ? $product->image
                    : get_image_upload('Product/' . $product->image))
                : asset('placeholder/product/image.png'),
            'images'                    => $gallery_arr,
            'variant_images'            => $variantImages,

            'attribute_id'              => $product->attribute_id,
            'attribute_title'           => $attribute_title,
            'product_variant'           => $product_variant_arrs,

            'sub_variant_attribute_id'  => $product->sub_variant_attribute_id,
            'sub_attribute_title'       => $sub_attribute_title,
            'product_sub_variant'       => $product_sub_variant_arrs,
            'pro_sub_variant_item_arrs' => $pro_sub_variant_item_arrs,
            

            'short_decripation'         => $product->short_decripation,
            'description'               => $product->description,
            'product_type'              => $product->product_type,
            'quantity'                  => $product->quantity,
            'stock'                     => $stock,
            'stock_status'              => $is_stock_available,
            'discount_price'            => $product->discount_price,
            'regular_price'             => $product->regular_price,
            'sku'                       => $product->sku,
            'category'                  => $product->category,
            // 'top_level_category_id'     => $product->top_level_category_id,
            // 'mid_level_category_id'     => $product->mid_level_category_id,
        ];

        $isWishlisted = false;
        if (Auth::guard('customers')->check()) {
            $isWishlisted = Wishlist::where('customer_id', Auth::guard('customers')->id())
                                ->where('product_id', $product->id)
                                ->exists();
        }

        return view('front.shop.product-detail', compact(
            'get_product',
            'breadcrumbs_arrs',
            'isWishlisted'
        ));
    }
    
    public function variable_product_price(Request $request)
    {
        $product_id = checkDecrypt($request->product_id);
        $variant_name1 = $request->variant_name1;
        $ProducSubtVariantItem = ProducSubtVariantItems::where([ 'product_id' => $product_id, 'product_variant_id' => $variant_name1  ]);
        if( $request->variant_name2 != '' ){
            $variant_name2 = $request->variant_name2;
            $ProducSubtVariantItem->where(['product_sub_variant_id' => $variant_name2 ]);
        }
        $ProducSubtVariantItem = $ProducSubtVariantItem
            ->where('status', 'active')
            ->where('is_deleted', 0)
            ->first();

        // No matching (active) variant combination — treat as unavailable rather than crashing
        if (!$ProducSubtVariantItem) {
            return response()->json([
                'error'   => false,
                'html'    => '<h5><span>$</span>0.00</h5>',
                'stock'   => 0,
                'price'   => 0,
            ]);
        }

        $finalPrice     = calculateProductPrice($ProducSubtVariantItem->price);
        $finalPriceIncl = calculatePriceInclGst($finalPrice);

        $html = '';
        $html.= '<h5><span>$</span>'.number_format($finalPrice, 2).' <span class="gst_label">(Excl. GST)</span></h5>';
        $html.= '<h6 class="price_gst_incl"><span>$</span>'.number_format($finalPriceIncl, 2).' <span class="gst_label">(Incl. GST)</span></h6>';
        return response()->json([
            'error'   => false,
            'message' => 'Price updated',
            'html'    => $html,
            'stock'   => (int) $ProducSubtVariantItem->stock,
            'price'   => (float) $ProducSubtVariantItem->price,
        ]);
    } 

}
