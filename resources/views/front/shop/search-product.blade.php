 @if ( count($get_products) > 0 )
    <div class="selling_secondrow single_categorycart" >
        @foreach ($get_products as $key => $value)

            @php
                if ($value->product_type == 'variable') {
                    $minVariant    = $value->subVariantItems->first(); // already sorted asc
                    $originalPrice = $minVariant->price ?? 0;
                    $salePrice     = null;
                    $finalPrice    = calculateProductPrice($originalPrice);
                    $isVariable    = true;
                } else {
                    $originalPrice = $value->regular_price;
                    $salePrice     = $value->discount_price;
                    $finalPrice    = calculateProductPrice($originalPrice, $salePrice);
                    $isVariable    = false;
                }
                $isWishlisted = in_array($value->id, $wishlistedIds ?? []);
            @endphp

            <div class="product_card">
                <div class="common_loader">
                    <div>
                        <img src="{{ asset('assets/images/loader.gif') }}">
                    </div>
                </div>

                <div class="product_singlecatimg">
                    <div class="add_favourite">
                        <div class="like_icon">
                            @if(Auth::guard('customers')->check())
                                <a href="javascript:void(0);"
                                   class="WishlistToggle"
                                   data-id="{{ encrypt($value->id) }}"
                                   data-wishlisted="{{ $isWishlisted ? '1' : '0' }}">
                                    <img src="{{ asset($isWishlisted ? 'assets/dist/images/heart_filled.svg' : 'assets/dist/images/cart_likeicon.svg') }}"
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
                        <a href="{{ route('product-detail', $value->slug) }}">
                            @php
                                $productImg = $value->image != ''
                                    ? ((str_starts_with($value->image, 'http://') || str_starts_with($value->image, 'https://'))
                                        ? $value->image
                                        : get_image_upload('Product/' . $value->image))
                                    : asset('placeholder/product/image.png');
                            @endphp
                            <img src="{{ $productImg }}">
                        </a>
                    </div>
                </div>

                <div class="product_singleinfo">
                    <div class="product_info">
                        <h5>{{ $get_bottom_category->title }}</h5>
                        <a href="{{ route('product-detail', $value->slug) }}" > {{ $value->title }}</a>
                        <p>{{ $value->short_decripation }}</p>
                    </div>
                    <div class="product_price">
                        <div class="change_price">
                           
                           

                            @if($salePrice != '')
                                <p> ${{ number_format($originalPrice, 2) }} </p>
                            @endif
                            <h6>
                                <span>$</span>
                                {{ number_format($finalPrice, 2) }}
                            </h6>
                        </div> 
                        <div class="add_tocartbtn">
                            @if(Auth::guard('customers')->check())
                                @if( $value->product_type == 'variable' )
                                <a href="{{ route('product-detail', $value->slug) }}">
                                @else
                                <a  class="AddToCartAction" data-id="{{ encrypt($value->id) }}">
                                @endif
                            @else
                                <a data-bs-toggle="modal" data-bs-target="#loginModal">
                            @endif
                                <span>Add to Card</span>
                                <div class="btncart_icon">
                                    <img src="{{ asset('assets/dist/images/addtocart-icon.svg') }}">
                                </div>
                            </a>
                        </div>
                    </div>
                </div>    
            </div>
        @endforeach
    </div>
    <div class="pagination-wrapper">
        <div class="custome_paginate mt-4">
            {{ $get_products->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
@else
    <div class="not_found">
         Product not found 
    </div>
@endif