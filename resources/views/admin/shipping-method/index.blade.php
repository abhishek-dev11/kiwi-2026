@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                <form action="{{ route('admin.shipping-methods') }}" method="post" id="product_filter" class="">
                    @csrf
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }}</label>
                                <input class="form-control" name="filter_shipping_method_title" type="text" value="{{ $common['filter_shipping_method_title']  }}" placeholder="{{ translate('Enter Title') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="filter_shipping_method_status">{{ translate('Status') }}</label>
                                <select id="filter_shipping_method_status" name="filter_shipping_method_status" class="form-select single-select" data-allow-clear="true">
                                    <option value="">{{ translate('Select Status') }}</option>
                                    <option value="Active" {{ getSelected('Active', $common['filter_shipping_method_status']) }}>{{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $common['filter_shipping_method_status']) }}>{{ translate('Deactive') }}</option>
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
                <a href="{{ route('admin.shipping-method.add') }}" class="dt-button create-new btn btn-primary float-end">
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
                            <th>{{ translate('Title') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th>{{ translate('Rate') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th style="text-align: center;">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($getShippingMethods->isNotEmpty())
                            @foreach ($getShippingMethods as $key => $value)
                                <tr>
                                    <td>{{ $getShippingMethods->firstItem() + $key }}</td>
                                    <td><strong>{{ $value['title'] }}</strong></td>
                                    <td>{{ $value['type'] == 'weight_based' ? translate('Weight Based') : translate('Flat Rate') }}</td>
                                    <td>
                                        @if($value['type'] == 'flat')
                                            {{ get_currency_symbol() }}{{ number_format($value['flat_rate'], 2) }}
                                        @else
                                            @php $bracketCount = $value->weightRates()->count(); @endphp
                                            {{ $bracketCount }} {{ $bracketCount == 1 ? translate('bracket') : translate('brackets') }}
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
                                                <a class="dropdown-item" href="{{ route('admin.shipping-method.edit', encrypt($value['id'])) }}">
                                                    <i class="bx bx-edit-alt me-1"></i>
                                                    {{ translate('Edit') }}
                                                </a>

                                                <a class="dropdown-item confirm-delete" href="javascript:void(0);" data-href="{{ route('admin.shipping-method.delete', encrypt($value['id'])) }}" >
                                                    <i class="bx bx-trash me-1"></i>{{ translate('Delete') }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">{{ translate('No record Found') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="row">
                    <div class="dataTables_paginate paging_simple_numbers mt-4">
                        {{ $getShippingMethods->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
