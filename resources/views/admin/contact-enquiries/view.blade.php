@extends('admin.layout.master')
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>
                <a href="{{ route('admin.contact-enquiries') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th style="width:150px;">{{ translate('Name') }}</th>
                        <td>{{ $Enquiry->name }}</td>
                    </tr>
                    <tr>
                        <th>{{ translate('Email') }}</th>
                        <td><a href="mailto:{{ $Enquiry->email }}">{{ $Enquiry->email }}</a></td>
                    </tr>
                    <tr>
                        <th>{{ translate('Phone') }}</th>
                        <td>{{ $Enquiry->phone ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{ translate('Status') }}</th>
                        <td>{!! checkStatus($Enquiry->status) !!}</td>
                    </tr>
                    <tr>
                        <th>{{ translate('Submitted On') }}</th>
                        <td>{{ \Carbon\Carbon::parse($Enquiry->created_at)->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>{{ translate('Message') }}</th>
                        <td style="white-space: pre-line;">{{ $Enquiry->message }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
