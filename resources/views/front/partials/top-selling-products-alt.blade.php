<?php
    $randomTopSellingProducts = App\Models\Products::where(ConditionQuery())
        ->with(['subVariantItems' => function ($q) {
            $q->where('status', 'active')->where('is_deleted', 0)->orderBy('price', 'asc');
        }])
        ->inRandomOrder()
        ->limit(6)
        ->get();

    $randomTopSellingCategoryTitles = App\Models\Categories::whereIn('id', $randomTopSellingProducts->pluck('category')->filter()->unique())
        ->pluck('title', 'id');

    $randomTopSellingWishlistedIds = [];
    if (Auth::guard('customers')->check()) {
        $randomTopSellingWishlistedIds = App\Models\Wishlist::where('customer_id', Auth::guard('customers')->id())->pluck('product_id')->toArray();
    }
?>
@if($randomTopSellingProducts->count())
<div class="top_sellingsection">
    <div class="container">
        <div class="top_selltoprow">
            <h2 class="common_heading">Top Selling <span>Products</span></h2>
            <div class="category_allbtn common_btn">
                <a href="{{ route('shop') }}">Browse</a>
            </div>
        </div>

        <div class="selling_secondrow selling_prodslider dots_none">

            @foreach ($randomTopSellingProducts as $rtsProduct)
                @php
                    if ($rtsProduct->product_type == 'variable') {
                        $rtsMinVariant    = $rtsProduct->subVariantItems->first();
                        $rtsOriginalPrice = $rtsMinVariant->price ?? 0;
                        $rtsSalePrice     = null;
                        $rtsFinalPrice    = calculateProductPrice($rtsOriginalPrice);
                    } else {
                        $rtsOriginalPrice = $rtsProduct->regular_price;
                        $rtsSalePrice     = $rtsProduct->discount_price;
                        $rtsFinalPrice    = calculateProductPrice($rtsOriginalPrice, $rtsSalePrice);
                    }
                    $rtsIsWishlisted = in_array($rtsProduct->id, $randomTopSellingWishlistedIds ?? []);
                @endphp

                <div class="slide">
                    <div class="product_card">
                        <div class="add_favourite">
                            <div class="like_icon">
                                @if(Auth::guard('customers')->check())
                                    <a href="javascript:void(0);"
                                       class="WishlistToggle"
                                       data-id="{{ encrypt($rtsProduct->id) }}"
                                       data-wishlisted="{{ $rtsIsWishlisted ? '1' : '0' }}">
                                        <img src="{{ asset($rtsIsWishlisted ? 'assets/dist/images/heart_filled.svg' : 'assets/dist/images/cart_likeicon.svg') }}"
                                             class="wishlist-icon">
                                    </a>
                                @else
                                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        <img src="{{ asset('assets/dist/images/cart_likeicon.svg') }}">
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="product_img">
                            <a href="{{ route('product-detail', $rtsProduct->slug) }}">
                                <img src="{{ resolve_product_image($rtsProduct->image) }}"
                                     onerror="this.onerror=null;this.src='{{ asset('placeholder/product/image.png') }}';">
                            </a>
                        </div>

                        <div class="product_info">
                            <h5>{{ $randomTopSellingCategoryTitles[$rtsProduct->category] ?? '' }}&nbsp;</h5>
                            <a href="{{ route('product-detail', $rtsProduct->slug) }}"> {{ $rtsProduct->title }}</a>
                            <p>{{ $rtsProduct->short_decripation }}</p>
                        </div>

                        <div class="product_price">
                            @if($rtsSalePrice != '' && $rtsSalePrice < $rtsOriginalPrice)
                                <p><s>${{ number_format($rtsOriginalPrice, 2) }}</s></p>
                            @endif
                            <h6><span>$</span>{{ number_format($rtsFinalPrice, 2) }}</h6>
                            @if(Auth::guard('customers')->check())
                                @if( $rtsProduct->product_type == 'variable' )
                                <a href="{{ route('product-detail', $rtsProduct->slug) }}">
                                @else
                                <a class="AddToCartAction" data-id="{{ encrypt($rtsProduct->id) }}">
                                @endif
                            @else
                                <a data-bs-toggle="modal" data-bs-target="#loginModal">
                            @endif
                                Add to Cart
                                <div class="btncart_icon">
                                    <img src="{{ asset('assets/dist/images/addtocart-icon.svg') }}">
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>
@endif
