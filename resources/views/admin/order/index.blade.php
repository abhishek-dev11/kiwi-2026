@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">

                <form action="{{ route('admin.order') }}" method="post" id="product_filter" class="">
                    @csrf

                    <div class="row gy-4 gy-sm-1">
                        <div class="col-md-3">
                            <div class="form-group ">
                                <label class="col-form-label">{{ translate('Order ID') }}</label>
                                <input class="form-control" name="filter_order_id" type="text" value="{{ $common['filter_order_id']  }}" placeholder="{{ translate('Enter Order ID') }}">
                            </div>
                        </div>


                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="col-form-label" for="status">{{ translate('Order Status') }}</label>
                                <select id="status" name="filter_order_status" class="form-select single-select" data-allow-clear="true">
                                    <option value="" > {{ translate('Select Order Status') }}</option>
                                    @php
                                    $getOrderStatus = getOrderStatus();
                                    @endphp
                                    @foreach($getOrderStatus as $OrderStatus)
                                    <option value="{{ $OrderStatus }}" {{ getSelected($OrderStatus, $common['filter_order_status']) }}>{{ strtoupper($OrderStatus) }}</option>
                                    @endforeach
                                    
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col-md-12 d-flex justify-content-end mt-4">
                            <div class="filter_button my-auto">
                                <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4" name="is_filter" value="1"><i class="fa fa-filter" aria-hidden="true"></i></button>
                                <button type="submit" class="btn btn-primary waves-effect waves-light m-r-10 fs-4" name="reset" value="1"><i class="fa fa-undo" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>



        <div class="card shadow mb-4">
             <div class="card-header">
                <div class="entries_div">
                    <strong class="card-title f-18">{{ $common['title'] }}</strong>
                </div>
            </div>

            {{-- table-responsive --}}
            <div class=" text-nowrap dataTables_wrapper ">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ translate('Order ID') }}</th>
                            <th>{{ translate('Date') }}</th>
                            <th>{{ translate('Customer') }}</th>
                            <th>{{ translate('Payment Method') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Total') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($getOrders->isNotEmpty())
                            @foreach ($orders as $key => $value)
                                <tr class=" @if($value['status_color']) {{ $value['status_color'] }} @endif " > 
                                    <td>#{{ $value['id'] }}</td>
                                    <td>{{ date('d F,Y',strtotime($value['created_at'])) }}</td>
                                    <td>{{ $value['full_name'] }}</td>
                                    <td>{{ strtoupper($value['payment_method']) }}</td>
                                    <td>{{ strtoupper($value['order_status']) }}</td>
                                    <td>{{get_currency_symbol()}} {{ $value['total'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.order.details',encrypt($value['id'])) }}"><i class="fa fa-eye text-primary cursur-pointer" aria-hidden="true"></i></a>
                                        <a href="{{ route('admin.order.invoice',encrypt($value['id'])) }}"><i class="fa fa-download text-primary cursur-pointer" aria-hidden="true" style="margin-left: 20px;"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">{{ translate('No record Found') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="row">
                    <div class="dataTables_paginate paging_simple_numbers mt-4">
                        {{ $getOrders->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection