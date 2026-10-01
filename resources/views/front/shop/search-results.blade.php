@extends('front.layout.master')
@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="javascript:void(0);">Search Results</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="allCategory_mainsec single_categorypg">
    <div class="container">
    <div class="filter_right">

        <div class="category_toprow">
            <div class="category_left">
                @if($q !== '')
                    <h2>Search results for &ldquo;{{ $q }}&rdquo;</h2>
                    <p>{{ $products->total() }} Results found</p>
                @else
                    <h2>Search</h2>
                @endif
            </div>
        </div>

        @if($q === '')
            <div class="not_found">Please enter a search term.</div>
        @else

            @if($categories->count() > 0)
                <h5 class="mt-3 mb-3">Categories</h5>
                <div class="see_category">
                    @foreach($categories as $category)
                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="{{ $category['url'] }}">
                                    <img src="{{ $category['image'] ? get_image_upload('Category/'.$category['image']) : asset('placeholder/category/image.png') }}">
                                </a>
                            </div>
                            <h5>
                                <a href="{{ $category['url'] }}">{{ $category['title'] }}</a>
                            </h5>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($products->count() > 0)
                <h5 class="mt-3 mb-3">Products</h5>
                <div class="selling_secondrow single_categorycart">
                    @foreach($products as $value)
                        @php
                            if ($value->product_type == 'variable') {
                                $minVariant    = $value->subVariantItems->first();
                                $originalPrice = $minVariant->price ?? 0;
                                $salePrice     = null;
                                $finalPrice    = calculateProductPrice($originalPrice);
                            } else {
                                $originalPrice = $value->regular_price;
                                $salePrice     = $value->discount_price;
                                $finalPrice    = calculateProductPrice($originalPrice, $salePrice);
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
                                    <h5>{{ $productCategoryTitles[$value->id] ?? '' }}</h5>
                                    <a href="{{ route('product-detail', $value->slug) }}"> {{ $value->title }}</a>
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
                                            <a class="AddToCartAction" data-id="{{ encrypt($value->id) }}">
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
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @elseif($categories->count() === 0)
                <div class="not_found">
                    No results found for &ldquo;{{ $q }}&rdquo;
                </div>
            @endif
        @endif

    </div>
    </div>
</div>

@endsection
