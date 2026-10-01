@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                <form action="{{ route('admin.coupons') }}" method="post" id="product_filter" class="">
                    @csrf
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Coupon Code') }}</label>
                                <input class="form-control" name="filter_coupon_code" type="text" value="{{ $common['filter_coupon_code']  }}" placeholder="{{ translate('Enter Code') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="filter_coupon_status">{{ translate('Status') }}</label>
                                <select id="filter_coupon_status" name="filter_coupon_status" class="form-select single-select" data-allow-clear="true">
                                    <option value="">{{ translate('Select Status') }}</option>
                                    <option value="Active" {{ getSelected('Active', $common['filter_coupon_status']) }}>{{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $common['filter_coupon_status']) }}>{{ translate('Deactive') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex justify-content-left my-auto">
                            <div class="filter_button mt-4">
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
                <strong class="card-title f-18">{{ $common['title'] }}</strong>
                <a href="{{ route('admin.coupon.add') }}" class="dt-button create-new btn btn-primary float-end">
                    <span>
                        <i class="bx bx-plus me-sm-1"></i>
                        <span class="d-none d-sm-inline-block">{{ translate('Add New') }}</span>
                    </span>
                </a>
            </div>

            <div class=" text-nowrap dataTables_wrapper ">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Code') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Value') }}</th>
                            <th>{{ translate('Min Order') }}</th>
                            <th>{{ translate('Usage') }}</th>
                            <th>{{ translate('Validity') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th style="text-align: center;">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($getCoupons->isNotEmpty())
                            @foreach ($getCoupons as $key => $value)
                                <tr>
                                    <td>{{ $getCoupons->firstItem() + $key }}</td>
                                    <td><strong>{{ $value['code'] }}</strong></td>
                                    <td>{{ ucfirst($value['type']) }}</td>
                                    <td>
                                        @if($value['type'] == 'percentage')
                                            {{ rtrim(rtrim($value['value'], '0'), '.') }}%
                                            @if($value['max_discount_amount'])
                                                <div class="text-muted" style="font-size: 12px;">Max {{ get_currency_symbol() }}{{ number_format($value['max_discount_amount'], 2) }}</div>
                                            @endif
                                        @else
                                            {{ get_currency_symbol() }}{{ number_format($value['value'], 2) }}
                                        @endif
                                    </td>
                                    <td>{{ $value['min_order_amount'] ? get_currency_symbol().number_format($value['min_order_amount'], 2) : '-' }}</td>
                                    <td>{{ $value['used_count'] }} / {{ $value['usage_limit'] ?? '&infin;' }}</td>
                                    <td>
                                        @if($value['start_date'] || $value['end_date'])
                                            {{ $value['start_date'] ? date('d M Y', strtotime($value['start_date'])) : '-' }}
                                            &ndash;
                                            {{ $value['end_date'] ? date('d M Y', strtotime($value['end_date'])) : '-' }}
                                        @else
                                            {{ translate('No Expiry') }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $value['status'] == 'Active' ? 'bg-label-success' : 'bg-label-danger' }}">{{ translate($value['status']) }}</span>
                                    </td>
                                    <td class="d-flex justify-content-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('admin.coupon.edit', encrypt($value['id'])) }}">
                                                    <i class="bx bx-edit-alt me-1"></i>
                                                    {{ translate('Edit') }}
                                                </a>

                                                <a class="dropdown-item confirm-delete" href="javascript:void(0);" data-href="{{ route('admin.coupon.delete', encrypt($value['id'])) }}" >
                                                    <i class="bx bx-trash me-1"></i>{{ translate('Delete') }}
                                                </a>
                                            </div>
                                        </div>
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
                        {{ $getCoupons->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
