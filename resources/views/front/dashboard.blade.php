@extends('front.layout.master')
@section('content')


<?php 
//dd($Orders);
?>
<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('user.dashboard') }}">My Account</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="{{ route('user.dashboard') }}">My Orders</a></li>
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
                <div class="order_rightcol">
                    <div class="top_orderrow">
                        <h2>My Orders</h2>
                        <form action="" method="post" id="" class="">
                            @csrf
                            <div class="checkorder_details">
                                <div class="order_search">
                                    <img src="{{ asset('assets/dist/images/search-icon.svg') }}">
                                    <input class="form-control" type="number" name="filter_front_order_keyword" value="{{ $common['filter_front_order_keyword']  }}" placeholder="{{ translate('Search Order ID') }}">
                                </div>
                                <div class="sortby_info"> 
                                    <select id="status" name="filter_front_order_status" class="sort_dropdown" data-allow-clear="true">
                                        <option value="" > {{ translate('Order Status') }}</option>
                                        @php
                                        $getOrderStatus = getOrderStatus();
                                        @endphp
                                        @foreach($getOrderStatus as $OrderStatus)
                                        <option value="{{ $OrderStatus }}" {{ getSelected($OrderStatus, $common['filter_front_order_status']) }}>{{ strtoupper($OrderStatus) }}</option>
                                        @endforeach
                                    </select>
                                  

                                </div>

                                <div class="field--z">
                                    <input type="text" id="filter_startdate" name="filter_front_order_date_from" placeholder="From Date"  value="{{ $common['filter_front_order_date_from']  }}" >
                                </div>

                                <div class="field--z">
                                    <input type="text" id="filter_enddate" name="filter_front_order_date_to" placeholder="To Date" value="{{ $common['filter_front_order_date_to']  }}" >
                                </div>
                                <div class=" orange_btn">
                                    <button type="submit" class="" name="is_filter" value="1"> Find </button>
                                    <button type="submit" class="" name="reset" value="1"><i class="fa fa-undo" aria-hidden="true"></i></button>
                                </div>   
                            </div>
                        </form>
                    </div>
                    <div class="orderinfo_table table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Order ID</th>
                                    <th>Order Date</th>
                                    <th>Status</th>
                                    <th>Payment Method</th>
                                    <th>Total Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if( $Orders->isNotEmpty() )
                                    @foreach ($order_arrs as $key => $value)
                                        <tr>
                                            <td>{{ $value['sn'] }}</td>
                                            <td>#{{ $value['id'] }}</td>
                                            <td>{{ date('d-m-Y',strtotime($value['order_date'])) }} </td>
                                            <td>{{ $value['order_status'] }}</td>
                                            <td style="text-transform: capitalize;">{{ $value['payment_method'] }}</td>
                                            <td>${{ $value['total'] }}</td>
                                            <td>
                                                <a href="{{ route('user.order-detail', encrypt($value['id']) ) }}" class="view_status">
                                                    <img src="{{ asset('assets/dist/images/view-icon.svg') }}">
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">{{ translate('Record not found') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if ($Orders->isNotEmpty()) 
                        <div class="pagination-wrapper">
                            <div class="custome_paginate mt-4">
                                {{ $Orders->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>


            </div>
        </div>
    </div>
</div>

@endsection