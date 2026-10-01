@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">



        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                <form action="{{ route('admin.suppliers') }}" method="post" id="product_filter" class="">
                    @csrf
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Full Name') }}</label>
                                <input class="form-control" name="filter_supplier_name" type="text" value="{{ $common['filter_supplier_name']  }}" placeholder="{{ translate('Enter Name') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Email') }}</label>
                                <input class="form-control" name="filter_supplier_email" type="text" value="{{ $common['filter_supplier_email']  }}" placeholder="{{ translate('Enter Email') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Type') }}</label>
                                <select name="filter_supplier_type" class="form-select">
                                    <option value="">{{ translate('All') }}</option>
                                    <option value="csv" {{ $common['filter_supplier_type'] == 'csv' ? 'selected' : '' }}>{{ translate('CSV') }}</option>
                                    <option value="api" {{ $common['filter_supplier_type'] == 'api' ? 'selected' : '' }}>{{ translate('API') }}</option>
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
                <a href="{{ route('admin.supplier.add') }}" class="dt-button create-new btn btn-primary float-end">
                    <span>
                        <i class="bx bx-plus me-sm-1"></i>
                        <span class="d-none d-sm-inline-block">{{ translate('Add New') }}</span>
                    </span>
                </a>
            </div>

            {{-- table-responsive --}}
            <div class=" text-nowrap dataTables_wrapper ">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Full Name') }}</th>
                            <th>{{ translate('Email') }}</th>
                            <th>{{ translate('Phone') }}</th>
                            <th>{{ translate('Type') }}</th>
                            <th style="text-align: center;">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($getSuppliers->isNotEmpty())
                            @foreach ($getSuppliers as $key => $value)
                                <tr>
                                    <td>{{ $getSuppliers->firstItem() + $key }}</td>
                                    <td>
                                        {{ ($value['full_name']) ? $value['full_name'] : '-' }}
                                    </td>
                                    <td>{{ ($value['email']) ? $value['email'] : '-' }}</td>
                                    <td>{{ ($value['phone']) ? $value['phone'] : '-' }}</td>
                                    <td>
                                        @if ($value['type'] == 'api')
                                            <span class="badge bg-label-primary">{{ translate('API') }}</span>
                                        @elseif ($value['type'] == 'csv')
                                            <span class="badge bg-label-secondary">{{ translate('CSV') }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="d-flex justify-content-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('admin.supplier.edit', encrypt($value['id'])) }}">
                                                    <i class="bx bx-edit-alt me-1"></i> 
                                                    {{ translate('Edit') }}
                                                </a>

                                                <a class="dropdown-item confirm-delete" href="javascript:void(0);" data-href="{{ route('admin.supplier.delete', encrypt($value['id'])) }}" >
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
                        {{ $getSuppliers->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection
