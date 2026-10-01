<?php
    // Only genuine same-category matches are shown - no random padding, so this
    // section may show fewer than 6 cards, or hide entirely if none exist.
    $similarProducts = App\Models\Products::where(ConditionQuery())
        ->when($categoryId, function ($q) use ($categoryId) {
            $q->where('category', $categoryId);
        })
        ->when($excludeProductId, function ($q) use ($excludeProductId) {
            $q->where('id', '!=', $excludeProductId);
        })
        ->with(['subVariantItems' => function ($q) {
            $q->where('status', 'active')->where('is_deleted', 0)->orderBy('price', 'asc');
        }])
        ->inRandomOrder()
        ->limit(6)
        ->get();

    $similarProductsCategoryTitles = App\Models\Categories::whereIn('id', $similarProducts->pluck('category')->filter()->unique())
        ->pluck('title', 'id');

    $similarProductsWishlistedIds = [];
    if (Auth::guard('customers')->check()) {
        $similarProductsWishlistedIds = App\Models\Wishlist::where('customer_id', Auth::guard('customers')->id())->pluck('product_id')->toArray();
    }
?>
@if($similarProducts->count())
<div class="top_sellingsection">
    <div class="container">
        <div class="top_selltoprow">
            <h2 class="common_heading">Similar <span>Products</span></h2>
            <div class="category_allbtn common_btn">
                <a href="{{ route('shop') }}">Browse</a>
            </div>
        </div>

        <div class="selling_secondrow selling_prodslider dots_none">

            @foreach ($similarProducts as $spProduct)
                @php
                    if ($spProduct->product_type == 'variable') {
                        $spMinVariant    = $spProduct->subVariantItems->first();
                        $spOriginalPrice = $spMinVariant->price ?? 0;
                        $spSalePrice     = null;
                        $spFinalPrice    = calculateProductPrice($spOriginalPrice);
                    } else {
                        $spOriginalPrice = $spProduct->regular_price;
                        $spSalePrice     = $spProduct->discount_price;
                        $spFinalPrice    = calculateProductPrice($spOriginalPrice, $spSalePrice);
                    }
                    $spIsWishlisted = in_array($spProduct->id, $similarProductsWishlistedIds ?? []);
                @endphp

                <div class="slide">
                    <div class="product_card">
                        <div class="add_favourite">
                            <div class="like_icon">
                                @if(Auth::guard('customers')->check())
                                    <a href="javascript:void(0);"
                                       class="WishlistToggle"
                                       data-id="{{ encrypt($spProduct->id) }}"
                                       data-wishlisted="{{ $spIsWishlisted ? '1' : '0' }}">
                                        <img src="{{ asset($spIsWishlisted ? 'assets/dist/images/heart_filled.svg' : 'assets/dist/images/cart_likeicon.svg') }}"
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
                            <a href="{{ route('product-detail', $spProduct->slug) }}">
                                <img src="{{ resolve_product_image($spProduct->image) }}"
                                     onerror="this.onerror=null;this.src='{{ asset('placeholder/product/image.png') }}';">
                            </a>
                        </div>

                        <div class="product_info">
                            <h5>{{ $similarProductsCategoryTitles[$spProduct->category] ?? '' }}&nbsp;</h5>
                            <a href="{{ route('product-detail', $spProduct->slug) }}"> {{ $spProduct->title }}</a>
                            <p>{{ $spProduct->short_decripation }}</p>
                        </div>

                        <div class="product_price">
                            @if($spSalePrice != '' && $spSalePrice < $spOriginalPrice)
                                <p><s>${{ number_format($spOriginalPrice, 2) }}</s></p>
                            @endif
                            <h6><span>$</span>{{ number_format($spFinalPrice, 2) }}</h6>
                            @if(Auth::guard('customers')->check())
                                @if( $spProduct->product_type == 'variable' )
                                <a href="{{ route('product-detail', $spProduct->slug) }}">
                                @else
                                <a class="AddToCartAction" data-id="{{ encrypt($spProduct->id) }}">
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
