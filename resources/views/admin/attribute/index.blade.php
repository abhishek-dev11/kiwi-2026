@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">

                <form action="{{ route('admin.attributes') }}" method="post" id="product_filter" class="">
                    @csrf
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Title') }}</label>
                                <input class="form-control" name="filter_attribute_title" type="text" value="{{ $common['filter_attribute_title']  }}" placeholder="{{ translate('Enter title') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-3">
                                <label class="col-form-label" for="status">{{ translate('Status') }}</label>
                                <select id="status" name="filter_attribute_status" class="form-select single-select" data-allow-clear="true">
                                    <option value="Active" {{ getSelected('Active', $common['filter_attribute_status']) }}> {{ translate('Active') }}</option>
                                    <option value="Deactive" {{ getSelected('Deactive', $common['filter_attribute_status']) }}>{{ translate('Deactive') }}</option>
                                </select>
                            </div>
                        </div>
                   
                        <div class="col-md-3 d-flex justify-content-left mt-4">
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
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.attribute.add') }}" class="dt-button create-new btn btn-primary float-end">
                    <span><i class="bx bx-plus me-sm-1"></i>
                        <span class="d-none d-sm-inline-block">{{ translate('Add New') }}</span></span>
                </a>

            </div>

            {{-- table-responsive --}}
            <div class=" text-nowrap dataTables_wrapper ">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Title') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($Attributes->isNotEmpty())
                            @foreach ($Attributes as $key => $value)
                                <tr>
                                    <td>{{ $Attributes->firstItem() + $key }}</td>
                                    <td>{{ $value['title'] }}</td>
                                    <td>{!! checkStatus($value['status']) !!}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.attribute.edit', encrypt($value['id'])) }}"><i
                                                        class="bx bx-edit-alt me-1"></i> {{ translate('Edit') }}</a>
                                                <a class="dropdown-item confirm-delete" href="javascript:void(0);"
                                                    data-href="{{ route('admin.attribute.delete', encrypt($value['id'])) }}"><i
                                                        class="bx bx-trash me-1"></i>{{ translate('Delete') }} </a>
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
                        {{ $Attributes->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection
