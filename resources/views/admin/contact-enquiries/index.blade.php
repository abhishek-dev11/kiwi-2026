@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-4">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                <form action="{{ route('admin.contact-enquiries') }}" method="post" id="enquiry_filter" class="">
                    @csrf
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Name') }}</label>
                                <input class="form-control" name="filter_enquiry_name" type="text" value="{{ $common['filter_enquiry_name'] }}" placeholder="{{ translate('Enter Name') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Email') }}</label>
                                <input class="form-control" name="filter_enquiry_email" type="text" value="{{ $common['filter_enquiry_email'] }}" placeholder="{{ translate('Enter Email') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Status') }}</label>
                                <select class="form-select" name="filter_enquiry_status">
                                    <option value="">{{ translate('All') }}</option>
                                    <option value="Pending" @if($common['filter_enquiry_status']=='Pending') selected @endif>{{ translate('Pending') }}</option>
                                    <option value="Resolved" @if($common['filter_enquiry_status']=='Resolved') selected @endif>{{ translate('Resolved') }}</option>
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
            </div>

            <div class=" text-nowrap dataTables_wrapper ">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ translate('Name') }}</th>
                            <th>{{ translate('Email') }}</th>
                            <th>{{ translate('Phone') }}</th>
                            <th>{{ translate('Message') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Date') }}</th>
                            <th style="text-align: center;">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if ($getEnquiries->isNotEmpty())
                            @foreach ($getEnquiries as $key => $value)
                                <tr>
                                    <td>{{ $getEnquiries->firstItem() + $key }}</td>
                                    <td>{{ $value['name'] ?: '-' }}</td>
                                    <td>{{ $value['email'] ?: '-' }}</td>
                                    <td>{{ $value['phone'] ?: '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($value['message'], 40) }}</td>
                                    <td>{!! checkStatus($value['status']) !!}</td>
                                    <td>{{ \Carbon\Carbon::parse($value['created_at'])->format('d M Y, h:i A') }}</td>
                                    <td class="d-flex justify-content-center">
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                data-bs-toggle="dropdown"><i
                                                    class="bx bx-dots-vertical-rounded"></i></button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('admin.contact-enquiry.view', encrypt($value['id'])) }}">
                                                    <i class="bx bx-show me-1"></i>
                                                    {{ translate('View') }}
                                                </a>

                                                <a class="dropdown-item confirm-delete" href="javascript:void(0);" data-href="{{ route('admin.contact-enquiry.delete', encrypt($value['id'])) }}" >
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
                        {{ $getEnquiries->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
