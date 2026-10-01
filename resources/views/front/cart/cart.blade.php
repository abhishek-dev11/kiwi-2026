@extends('front.layout.master')
@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="{{ route('cart') }}"> My Cart</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="subcategory_items">
    <div class="container">
        <div class="row" style="position: relative;">
			<div id="common_loader">
	            <div>
	                <img src="{{ asset('assets/images/loader.gif') }}">
	            </div>
	        </div>
            <div class="col-12 col-md-6 col-lg-8">
                <div class="cart-wrapper">
                    <div class="top_cartinnerrow">
	                    <h2>My Cart</h2>
	                    <table class="cart-table">
	                        <thead>
	                            <tr>
	                                <th class="product-col">Products</th>
	                                <th class="align_center">Price</th>
	                                <th class="align_center">Quantity</th>
	                                <th class="align_center">Subtotal</th>
	                                <th></th>
	                            </tr>
	                        </thead>
	                        <tbody id="cart-items-wrapper">
							    @include('front.cart.partials.cart-page-items', ['cart_arrs' => $cart_arrs])
							</tbody>
	                    </table>
                    </div>
 
                    {{-- Cart actions --}}
					<div class="cart-actions">
					    <a href="#" class="clear" id="clear-cart-btn">Clear Cart</a>
					    <a href="#" class="update" id="update-cart-btn">Update cart</a>
					</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="cart-side">
                	<div class="card card_height" id="cart-totals-wrapper">
					    @include('front.cart.partials.cart_totals', ['total_arr' => $total_arr])
					</div> 
                </div>
            </div>
        </div>
    </div>
</div>

@endsection