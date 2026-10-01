@extends('front.layout.master')
@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('user.dashboard') }}">My Account</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="{{ route('user.dashboard') }}">My Orders</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="">Order Details</a></li>
                 
            </ul>
        </div>
    </div>
</div>

<div class="order_detailssec">
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-8 col-lg-8">
                <div class="order_leftcol order_detailsleft">
                    <h4>Order Details</h4>
                    <hr>
                    <div class="order_innerinfo">
                        <div class="left_details">
                            <h6>Order ID : <span>#{{ $order->id }}</span></h6>
                            <div class="deliverd_status">
                                <img src="{{ asset('assets/dist/images/order-status.svg') }}" alt="">
                                <p class="delivered_on">
                                    <i class="delivered_icon"></i>
                                    @if($order->order_status == 'delivered')
                                        Order Delivered On: {{ \Carbon\Carbon::parse($order->delivered_at)->format('d - m - Y') }}
                                    @else
                                        Status: <strong>{{ ucfirst($order->order_status) }}</strong>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="right_details">
                            <p>Order Date: <span>{{ \Carbon\Carbon::parse($order->created_at)->format('d - m - Y') }}</span></p>
                            <a href="{{ route('user.order-invoice', encrypt($order->id) ) }}">
                                <img src="{{ asset('assets/dist/images/download.svg') }}">Download Invoice
                            </a>
                        </div>
                    </div>
                    <hr>
                    <h6>Orders Items</h6>
                    <div class="table-wrapper">
                        <table class="cart_table">
                            <tbody>
                                @foreach($order_products as $item)
                                    <!-- Row 1 -->
                                    <tr>
                                        <td class="cart_product">
                                            <div class="product_box">
                                                <img src="{{ asset('assets/dist/images/Shopping-Cartimg.png') }}" alt="">
                                                <div>
                                                    <h3>{{ $item->title }}</h3>
                                                    @if($item->product_sub_variant_id)
                                                        <p class="item_variant">
                                                            {{$item->attribute_title}}: {{ $item->product_variant_name }}
                                                        </p>
                                                    @endif
                                                    @if($item->product_sub_variant_id)
                                                        <p class="item_variant">
                                                            {{$item->sub_attribute_title}}: {{ $item->product_sub_variant_name }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="cart_qty">
                                            <p>Quantity</p>
                                            <h6>{{ $item->quantity }}</h6>
                                        </td>

                                        <td class="cart_price">
                                            <p>Price</p>
                                            <h6><span>$</span>{{ number_format($item->price, 2) }}</h6>
                                        </td>

                                        <td class="cart_total">
                                            <a href="#" class="reorder_btn">
                                                <img src="{{ asset('assets/dist/images/Reorder.svg') }}"> Reorder
                                            </a>
                                            <p>Total</p>
                                            <h6><span>$</span>{{ number_format($item->price * $item->quantity, 2) }}</h6>
                                        </td>

                                        <!-- <td class="cart_action">

                                        </td> -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    
                </div>

                <div class="order_leftcol order_detailsleft">
                    <h5>Order Summary</h5>
                    <hr>
                    <div class="order_summary">
                        <div class="summary_inner">
                            <p>Sub total</p>
                            <h6><span>$</span>{{ number_format($order_meta['sub_total'] ?? 0, 2) }}</h6>
                        </div>
                        <div class="summary_inner">
                            <p>Discount (10%)</p>
                            <h6>-<span>$</span>{{ number_format($order_meta['discount'] ?? 0, 2) }}</h6>
                        </div>

                        @if(!empty($order_meta['gst']))
                            <div class="summary_inner">
                                <p>GST (15%)</p>
                                <h6><span>$</span>{{ number_format($order_meta['gst'], 2) }}</h6>
                            </div>
                        @endif

                        
                        @if(!empty($order_meta['shipping']))
                            <div class="summary_inner">
                                <p>Shipping charges</p>
                                <h6><span>$</span>{{ number_format($order_meta['shipping'], 2) }}</h6>
                            </div>
                        @endif
                    </div>
                    <hr class="dark_hr">
                    <div class="ordersummary_total">
                        <p>Total</p>
                        <h6><span>$</span>{{ number_format($order_meta['total'] ?? $order->total, 2) }}</h6>
                    </div>
                </div>

                 <!-- Notes -->
                @if(!empty($order_meta['order_notes']))
                    <div class="order_leftcol order_detailsleft">
                        <h5>Notes</h5>
                        <hr>
                        <p class="notes_text">{{ $order_meta['order_notes'] }}</p>
                    </div> 
                @endif
            </div>

            <div class="col-12 col-md-3 col-lg-3">
                <div class="order_rightcol order_detailsright">
                    <h5>Customer Contact info</h5>
                    <hr>
                    <div class="orderd_innerright ">
                        <p>Name: <span> {{ $order->billing_first_name }} {{ $order->billing_last_name }} </span></p>
                        <p>Email: <span>{{ $order->billing_email }}</span></p>
                        <p>Phone: <span>{{ $order->billing_phone }}</span></p>
                    </div>
 
                </div>

                <div class="order_rightcol order_detailsright">
                    <h5>Status History timeline</h5>
                    <hr>
                    <div class="timeline">
                        @foreach($status_history as $history)
                            <div class="timeline-item">
                                <div class="title">{{ $history['status'] }}</div>
                                @if($history['date'])
                                <div class="date">{{ \Carbon\Carbon::parse($history['date'])->format('M d, h:i A') }}</div>
                                @endif
                            </div>
                        @endforeach

                        <!-- <div class="timeline-item">
                            <div class="title">Shipped</div>
                            <div class="date">Dec 14, 10:46 AM</div>
                        </div>

                        <div class="timeline-item">
                            <div class="title">Delivered</div>
                            <div class="date">Dec 16, 1:15 PM</div>
                        </div> -->
                    </div>
                </div>
 

                <!-- Billing Details -->
                <div class="order_rightcol order_detailsright">
                    <h5>Billing Details</h5>
                    <hr>
                    <div class="orderd_innerright ">
                        <p>Name: <span>{{ $order->billing_first_name }} {{ $order->billing_last_name }}</span></p>
                        <p>Email: <span>{{ $order->billing_email }}</span></p>
                        <p>Address: <span>{{ $order->billing_street_address }}</span></p>
                        <p>Pincode: <span>04015 </span></p>
                        <p>Country: <span>{{ $billing_country->name ?? '' }} </span></p>
                        <p>State: <span>{{ $billing_state->name ?? '' }} </span></p>
                        <p>City: <span>{{ $billing_city->name ?? '' }} </span></p>
                    </div>
                </div>

                <!-- Shipping Details -->
                <div class="order_rightcol order_detailsright">
                    <h5>Shipping Details</h5>
                    <hr>
                    <div class="orderd_innerright ">
                        <p>Name: <span>{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</span></p>
                        <p>Email: <span>{{ $order->shipping_email }}</span></p>
                        <p>Address: <span>{{ $order->shipping_street_address }}</span></p>
                        <p>Pincode: <span>04015 </span></p>
                        <p>Country: <span>{{ $shipping_country->name ?? '' }} </span></p>
                        <p>State: <span>{{ $shipping_state->name ?? '' }} </span></p>
                        <p>City: <span>{{ $shipping_city->name ?? '' }} </span></p>
                    </div>
                </div>

                <!-- Shipping Method -->
                @if(!empty($order_meta['shipping_method_id']))
                <div class="order_rightcol order_detailsright">
                    <h5>Shipping's</h5>
                    <hr>
                    <div class="orderd_innerright ">
                        <p><span>Standard Delivery (3–5 days)</span></p>
                        <p>Method: <span>{{ \App\Models\ShippingMethod::find($order_meta['shipping_method_id'])?->title ?? ('#' . $order_meta['shipping_method_id']) }}</span></p>
                    </div>
                </div>
                @endif

                <!-- Pay Method -->
                @if($order->payment_method != '')
                    <div class="order_rightcol order_detailsright">
                        <h5>Payment Method</h5>
                        <hr>
                        <div class="orderd_innerright ">
                            @if($order->payment_method == 'stripe')
                                Credit / Debit Card (Stripe)
                                @if(!empty($order->stripe_currency))
                                    <span>Currency: {{ strtoupper($order->stripe_currency) }}</span>
                                @endif
                            @else
                                <span>Cash on Delivery</span>
                            @endif
                        </div>
                    </div>
                @endif
                 
            </div>
        </div>
    </div>
</div>

 

@endsection