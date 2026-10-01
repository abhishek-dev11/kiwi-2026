@extends('front.layout.master')

@section('style')
<style>
.product-gallery .main-slider img {
    width: 100%;
    height: 420px;
    object-fit: contain;
    background: #f7f7f7;
    border-radius: 8px;
}
.product-gallery .thumb-slider {
    margin-top: 10px;
}
.product-gallery .thumb-slider .slick-slide {
    padding: 0 4px;
}
.product-gallery .thumb-slider img {
    width: 100%;
    height: 78px;
    object-fit: cover;
    cursor: pointer;
    border-radius: 6px;
    border: 2px solid #e0e0e0;
    transition: border-color .2s;
}
.product-gallery .thumb-slider .slick-current img {
    border-color: #333;
}
.price_gst_incl {
    color: #777;
    font-size: 14px;
    margin-top: 2px;
}
.gst_label {
    font-size: 12px;
    font-weight: 400;
    color: #999;
}
</style>
@endsection

@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                @if( count($breadcrumbs_arrs) > 0 )
                    @foreach( $breadcrumbs_arrs as $key => $value )
                        <li><span>/</span></li>
                        <li>
                            <a href="{{ route($value['route'], $value['params']) }}"> {{ $value['title'] }}</a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>

@php
    $variable_price      = '';
    $variable_price_incl = '';
    if ($get_product['product_type'] == 'variable' && count($get_product['pro_sub_variant_item_arrs']) > 0) {
        $prices   = array_column($get_product['pro_sub_variant_item_arrs'], 'price');
        $minPrice = calculateProductPrice(min($prices));
        $maxPrice = calculateProductPrice(max($prices));
        $variable_price = $minPrice == $maxPrice
            ? '<span>$</span>' . number_format($minPrice, 2)
            : '<span>$</span>' . number_format($minPrice, 2) . ' - <span>$</span>' . number_format($maxPrice, 2);

        $minPriceIncl = calculatePriceInclGst($minPrice);
        $maxPriceIncl = calculatePriceInclGst($maxPrice);
        $variable_price_incl = $minPriceIncl == $maxPriceIncl
            ? '<span>$</span>' . number_format($minPriceIncl, 2)
            : '<span>$</span>' . number_format($minPriceIncl, 2) . ' - <span>$</span>' . number_format($maxPriceIncl, 2);
    }
@endphp

<div class="subcategory_items product_details_main">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-6 col-lg-6">
                <div class="left_productimg">
                    <div class="product-gallery">
                        <div class="main-slider" id="productMainSlider"></div>
                        <div class="thumb-slider" id="productThumbSlider"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-6">
                <div class="right_productinfo">
                    <input type="hidden" name="product_id" id="product_id" value="{{ encrypt($get_product['id']) }}">
                    <div class="product_topinfo">
                        <h6>In: <span>{{ $get_product['category_title'] }}</span></h6>
                        <h3>{{ $get_product['title'] }}</h3>
                        <p>Category: <span>{{ $get_product['category_title'] }}</span></p>
                    </div>
                    <div class="stock_details">
                        <h6>Stock: <span class="stock_display">{{ $get_product['stock'] }}</span></h6>
                        <span>|</span>
                        <h6>SKU: {{ $get_product['sku'] }}</h6>
                    </div>
                    <hr>
                    @php
                        $originalPrice = $get_product['regular_price'];
                        $salePrice     = $get_product['discount_price'];
                        $finalPrice = calculateProductPrice(
                            $originalPrice,
                            $salePrice
                        );
                        $finalPriceIncl = calculatePriceInclGst($finalPrice);

                        // Disable Add to Cart / Buy Now when there's nothing sellable:
                        // simple product with no stock or a zero price, or a variable
                        // product with no priced variant combinations configured at all.
                        // For variable products with combinations, availability is re-checked
                        // per selection via the variable_product_price AJAX call.
                        $isUnavailable = $get_product['product_type'] == 'variable'
                            ? count($get_product['pro_sub_variant_item_arrs']) === 0
                            : ($get_product['quantity'] <= 0 || $finalPrice <= 0);
                    @endphp


                    <div class="price_details">
                        <div class="price_leftdeails">
                            <p>Price</p>
                            @if( $get_product['product_type'] == 'variable' && count($get_product['pro_sub_variant_item_arrs']) > 0 )
                                <div id="variable_price_id">
                                    <h5>{!! $variable_price !!} <span class="gst_label">(Excl. GST)</span></h5>
                                    <h6 class="price_gst_incl">{!! $variable_price_incl !!} <span class="gst_label">(Incl. GST)</span></h6>
                                </div>
                            @else
                                @if($salePrice != '')
                                    <h6> ${{ number_format($originalPrice, 2) }} </h6>
                                @endif
                                <h5><span>$</span>{{ number_format($finalPrice, 2) }} <span class="gst_label">(Excl. GST)</span></h5>
                                <h6 class="price_gst_incl"><span>$</span>{{ number_format($finalPriceIncl, 2) }} <span class="gst_label">(Incl. GST)</span></h6>
                            @endif
                        </div>
                        
                        @php
                            $isWishlisted = false;
                            if (Auth::guard('customers')->check()) {
                                $isWishlisted = \App\Models\Wishlist::where('customer_id', Auth::guard('customers')->id())
                                                    ->where('product_id', $get_product['id'])
                                                    ->exists();
                            }
                        @endphp
                        <div class="add_towaitbtn">
                            @if(Auth::guard('customers')->check())
                                <a href="javascript:void(0);"
                                   class="WishlistToggle"
                                   id="detailWishlistBtn"
                                   data-id="{{ encrypt($get_product['id']) }}"
                                   data-wishlisted="{{ $isWishlisted ? '1' : '0' }}">
                                    <span id="wishlistBtnText">{{ $isWishlisted ? 'Added' : 'Add' }}  to Wishlist</span>
                                    <img id="wishlistBtnIcon"
                                         src="{{ asset($isWishlisted ? 'assets/dist/images/heart_filled.svg' : 'assets/dist/images/blacklikeicon.png') }}">
                                </a>
                            @else
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#loginModal">
                                    <span>Add to Wishlist</span>
                                    <img src="{{ asset('assets/dist/images/blacklikeicon.png') }}">
                                </a>
                            @endif
                        </div>


                    </div>
                    <hr>
                    <div class="product_basicinfo">
                        @if( $get_product['product_type'] == 'variable' )
                            <div class="variant_group_wrap">

                                {{-- VARIANT 1 --}}
                                <div class="variant_group">
                                    <div class="sizelabel">
                                        <p>{{ $get_product['attribute_title'] }} <span></span></p>
                                    </div>
                                    <div class="sizes">
                                        @if( count($get_product['product_variant']) > 0 )
                                            @foreach( $get_product['product_variant'] as $variant )
                                                <div class="size-option">
                                                    <input
                                                        type="radio"
                                                        id="variant1_{{ $variant['id'] }}"
                                                        name="variant_name1"
                                                        class="variant_name1"
                                                        value="{{ $variant['id'] }}"
                                                    />
                                                    <label for="variant1_{{ $variant['id'] }}" class="size-label">
                                                        {{ $variant['value'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <span class="variant_error text-danger d-block mt-1"></span>
                                </div>

                                {{-- VARIANT 2 --}}
                                <div class="variant_group">
                                    <div class="sizelabel">
                                        <p>{{ $get_product['sub_attribute_title'] }} <span></span></p>
                                    </div>
                                    <div class="sizes">
                                        @if( count($get_product['product_sub_variant']) > 0 )
                                            @foreach( $get_product['product_sub_variant'] as $sub_variant )
                                                <div class="size-option">
                                                    <input
                                                        type="radio"
                                                        id="variant2_{{ $sub_variant['id'] }}"
                                                        name="variant_name2"
                                                        class="variant_name2"
                                                        value="{{ $sub_variant['id'] }}"
                                                    />
                                                    <label for="variant2_{{ $sub_variant['id'] }}" class="size-label">
                                                        {{ $sub_variant['value'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <span class="variant_error text-danger d-block mt-1"></span>
                                </div>

                            </div>
                        @endif

                        <div class="stepper">
                            <button class="minus">−</button>
                            <span>|</span>
                            <input type="number" name="" class="qty" value="1" min="1" />
                            <span>|</span>
                            <button class="plus">+</button>
                        </div>
                            
                        <div class="actions">
                            @if(Auth::guard('customers')->check())
                                <a href="javascript:void(0);" class="buy_btn BuyNowAction {{ $isUnavailable ? 'disabled' : '' }}" @if($isUnavailable) aria-disabled="true" @endif>Buy Now</a>
                            @else
                                <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#loginModal" class="buy_btn {{ $isUnavailable ? 'disabled' : '' }}" @if($isUnavailable) aria-disabled="true" @endif>Buy Now</a>
                            @endif
                            @if(Auth::guard('customers')->check())
                                <a class="cart_btn AddToCartAction {{ $isUnavailable ? 'disabled' : '' }}" @if($isUnavailable) aria-disabled="true" @endif>
                            @else
                                <a data-bs-toggle="modal" data-bs-target="#loginModal" class="cart_btn {{ $isUnavailable ? 'disabled' : '' }}" @if($isUnavailable) aria-disabled="true" @endif>
                            @endif
                                {{ $isUnavailable ? 'Out of Stock' : 'Add to Cart' }}
                                <img src="{{ asset('assets/dist/images/white-carticon.svg') }}">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="product_description">
    <div class="container">
        <div class="tabs">
            <div class="tab-header">
                <button class="tab-link active" data-tab="description">Description</button>
                @if( $get_product['product_type'] == 'variable' )
                    <button class="tab-link" data-tab="additional">Additional Information</button>
                @endif
            </div>

            <div class="tab-content active" id="description">
                {!! $get_product['description'] !!}
            </div>

            @if( $get_product['product_type'] == 'variable' )
                <div class="tab-content" id="additional">
                    <p>Additional product information goes here...</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('front.partials.similar-products', [
    'categoryId'      => $get_product['category'] ?? null,
    'excludeProductId' => $get_product['id'],
])

@endsection

@section('script')
<script>
    // ── Product Gallery (Slick) ──────────────────────────────────
    var productImage  = @json($get_product['product_image']);
    var galleryImages = @json($get_product['images']);
    var variantImages = @json($get_product['variant_images'] ?? []);
    var defaultImages = [productImage].concat(galleryImages).filter(Boolean);

    function buildSlides(images) {
        return images.map(function(src) {
            return '<div><img src="' + src + '" alt=""></div>';
        }).join('');
    }

    function initProductSlider(images) {
        var $main  = $('#productMainSlider');
        var $thumb = $('#productThumbSlider');

        // Destroy if already initialized
        if ($main.hasClass('slick-initialized'))  $main.slick('unslick');
        if ($thumb.hasClass('slick-initialized')) $thumb.slick('unslick');

        // Inject slides
        $main.html(buildSlides(images));
        $thumb.html(buildSlides(images));

        // Init main slider
        $main.slick({
            slidesToShow:   1,
            slidesToScroll: 1,
            arrows:         true,
            fade:           false,
            asNavFor:       '#productThumbSlider',
            lazyLoad:       'ondemand',
        });

        // Init thumbnail slider
        $thumb.slick({
            slidesToShow:   4,
            slidesToScroll: 1,
            asNavFor:       '#productMainSlider',
            dots:           false,
            arrows:         false,
            focusOnSelect:  true,
            responsive: [{
                breakpoint: 768,
                settings: { slidesToShow: 3 }
            }]
        });
    }

    // Color variant change → switch to variant images
    $(document).on('change', '.variant_name1', function() {
        var imgs = variantImages[$(this).val()];
        initProductSlider((imgs && imgs.length) ? imgs : defaultImages);
    });

    // Init on page load
    initProductSlider(defaultImages);
    // ─────────────────────────────────────────────────────────────

    // Wishlist toggle for detail page
    // Reuses the same WishlistToggle class & AJAX as product listing
    // BUT we need a specific loader for detail page (no .product_card wrapper)
    $('body').on('click', '#detailWishlistBtn', function(e) {
        e.preventDefault();

        let btn          = $(this);
        let product_id   = btn.data('id');
        let isWishlisted = btn.attr('data-wishlisted') == '1';

        const heartFilledIcon      = "{{ asset('assets/dist/images/heart_filled.svg') }}";
        const heartEmptyIcon       = "{{ asset('assets/dist/images/cart_likeicon.svg') }}";   // for product cards
        const heartEmptyDetailIcon = "{{ asset('assets/dist/images/blacklikeicon.png') }}";   // for detail page

        // Disable button to prevent double click
        btn.css('pointer-events', 'none').css('opacity', '0.6');

        $.ajax({
            type: 'POST',
            url:  "{{ route('add-to-wishlist') }}",
            data: {
                //_token:     $('meta[name="csrf-token"]').attr('content'),
                product_id: product_id,
            },
            success: function(response) {
                btn.css('pointer-events', 'auto').css('opacity', '1');

                if (response.error) {
                    danger_msg(response.message);
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    if (response.wishlisted) {
                        $('#wishlistBtnIcon').attr('src', heartFilledIcon);
                        $('#wishlistBtnText').text('Added to Wishlist');
                        btn.attr('data-wishlisted', '1');
                    } else {
                        $('#wishlistBtnIcon').attr('src', heartEmptyDetailIcon);
                        $('#wishlistBtnText').text('Add to Wishlist');
                        btn.attr('data-wishlisted', '0');
                    }
                    success_msg(response.message);

                    // Update wishlist count in header badge
                    /*if (typeof response.wishlist_count !== 'undefined') {
                        $('#TotalWishlistID').text(response.wishlist_count);
                    }*/
                }
            },
            error: function() {
                btn.css('pointer-events', 'auto').css('opacity', '1');
                danger_msg('Something went wrong!');
            }
        });
    });
</script>
@endsection