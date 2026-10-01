@extends('front.layout.master')
@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('user.dashboard') }}">My Account</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="{{ route('user.wishlist') }}">My Wishlist</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="order_detailssec">
    <div class="container">
        <div class="row">

            <div class="col-12 col-md-6 col-lg-3">
                @include('front.layout.sidebar')                  
            </div>

            <div class="col-12 col-md-6 col-lg-9">
                <div class="order_rightcol address_rightcol wishlit_rightcol">

                    <div class="wisthlist_toprow">
                        <div class="top_addressrow">
                            <h2>My Wishlist</h2>
                        </div>
                        <div class="">
                            <form method="GET" action="{{ route('user.wishlist') }}" class="bottom_wishlitrow">
                                <div class="order_search">
                                    <img src="{{ asset('assets/dist/images/search-icon.svg') }}">
                                    <input class="form-control"
                                           type="search"
                                           name="search"
                                           placeholder="Search"
                                           value="{{ $search ?? '' }}">
                                </div>
                                <div class="submit_btn orange_btn">
                                    <button type="submit">Find</button>
                                    <a href="{{ route('user.wishlist') }}" ><i class="fa fa-undo" aria-hidden="true"></i></a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="wishlist_table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Stock Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="wishlistItems">
                                @if(count($wishlist_arrs) > 0)
                                    @foreach($wishlist_arrs as $item)
                                        <tr id="wishlist_row_{{ $item['id'] }}">

                                            {{-- PRODUCT NAME --}}
                                            <td class="cart_product">
                                                <div class="product_box">
                                                    <div class="deleteaddress">
                                                        <a href="{{ route('remove-wishlist', encrypt($item['id'])) }}"
                                                           class="RemoveWishlist"
                                                           data-id="{{ $item['id'] }}">
                                                            <img src="{{ asset('assets/dist/images/delete.svg') }}">
                                                        </a>
                                                    </div>
                                                    <a href="{{ route('product-detail', $item['slug']) }}">
                                                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                                    </a>
                                                    <div>
                                                        <a href="{{ route('product-detail', $item['slug']) }}">
                                                            <h3>{{ $item['title'] }}</h3>
                                                        </a>
                                                        {{-- VARIANT NAMES --}}
                                                        @if($item['product_variant_name'] != '')
                                                            <p>
                                                                {{ $item['attribute_title'] }}: 
                                                                <span>{{ $item['product_variant_name'] }}</span>
                                                                @if($item['product_sub_variant_name'] != '')
                                                                    - {{ $item['sub_attribute_title'] }}: 
                                                                    <span>{{ $item['product_sub_variant_name'] }}</span>
                                                                @endif
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- PRICE --}}
                                            <td class="cart_price">
                                                @if($item['salePrice'] != '' && $item['salePrice'] != null)
                                                    <p>${{ number_format($item['originalPrice'], 2) }}</p>
                                                @endif
                                                <h6> <span>$</span>{{ number_format($item['finalPrice'], 2) }} </h6>
                                            </td>

                                            {{-- STOCK STATUS --}}
                                            <td>
                                                <div class="stock">
                                                    <span class="badge {{ $item['stock_status'] == 'In Stock' ? 'instock' : 'outstock' }}">
                                                        {{ $item['stock_status'] }}
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- ADD TO CART --}}
                                            <td>
                                                <div class="orange_btn">
                                                    @if($item['product_type'] == 'variable')
                                                        <a href="{{ route('product-detail', $item['slug']) }}">
                                                            Add to Card
                                                        </a>
                                                    @else
                                                        <a href="javascript:void(1)"
                                                           class="AddToCartAction"
                                                           data-id="{{ encrypt($item['product_id']) }}">
                                                            Add to Card
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach
                                @else
                                    <tr id="emptyWishlist">
                                        <td colspan="4">
                                            <div class="not_found">
                                                <p>Your wishlist is empty.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        @if ($get_wishlists->isNotEmpty()) 
                            <div class="pagination-wrapper">
                                <div class="custome_paginate mt-4">
                                    {{ $get_wishlists->appends(request()->query())->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection