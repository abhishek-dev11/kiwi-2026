@extends('front.layout.master')
@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('user.dashboard') }}">My Account</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="{{ route('user.order') }}">My Orders</a></li>
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
                        <div class="checkorder_details">
                            <div class="order_search">
                                <img src="{{ asset('assets/dist/images/search-icon.svg') }}">
                                <input class="form-control" type="search" placeholder="Search">
                            </div>
                            <div class="sortby_info">
                                <select class="sort_dropdown">
                                    <option value="">Sort by</option>
                                    <option value="apparel">Apparel</option>
                                    <option value="cafe">Cafe</option>
                                    <option value="office">Office Furniture</option>
                                    <option value="trade">Trade Events</option>
                                    <option value="hygiene">Hygiene</option>
                                </select>
                            </div>

                            <div class="field">
                                <input type="text" id="fromDate" placeholder="From Date"
                                    onfocus="this.type='date'"
                                    onblur="if(!this.value)this.type='text'">
                            </div>

                            <div class="field">
                                <input type="text" id="toDate" placeholder="To Date"
                                    onfocus="this.type='date'"
                                    onblur="if(!this.value)this.type='text'">
                            </div>
                            <div class=" orange_btn">
                                <a href="#" type="submit">Find</a>
                            </div>   
                        </div>
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
                                <?php
                                    for ($i = 1; $i <= 8; $i++) {
                                        ?>
                                            <tr>
                                                <td>0{{ $i }}</td>
                                                <td>#123456</td>
                                                <td>12 - 02 - 2026</td>
                                                <td><a href="#" class="shipped_status">Shipped</a></td>
                                                <td>Debit Card</td>
                                                <td>$145.80</td>
                                                <td>
                                                    <a href="" class="view_status">
                                                        <img src="{{ asset('assets/dist/images/view-icon.svg') }}">
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection