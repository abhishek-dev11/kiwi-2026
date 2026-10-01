@extends('admin.layout.master')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>
            </div>
            <div class="card-body">
                <form method="POST" class="relative_cls" action="{{ route('admin.shipping-settings') }}" id="shippingSettingsForm">
                    @csrf

                    <div class="row">

                        <!-- Company Name -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Company Name') }}</label>
                                <input class="form-control {{ $errors->has('origin_company_name') ? 'is-invalid' : '' }}" name="origin_company_name" id="origin_company_name"
                                    type="text" value="{{ old('origin_company_name', $get_shipping_setting->origin_company_name) }}"
                                    placeholder="{{ translate('e.g. Kiwi Business Products') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('origin_company_name') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Street Address -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Street Address') }}</label>
                                <input class="form-control {{ $errors->has('origin_street_address') ? 'is-invalid' : '' }}" name="origin_street_address" id="origin_street_address"
                                    type="text" value="{{ old('origin_street_address', $get_shipping_setting->origin_street_address) }}"
                                    placeholder="{{ translate('Enter Street Address') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('origin_street_address') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- City -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('City') }}</label>
                                <input class="form-control {{ $errors->has('origin_city') ? 'is-invalid' : '' }}" name="origin_city" id="origin_city"
                                    type="text" value="{{ old('origin_city', $get_shipping_setting->origin_city) }}"
                                    placeholder="{{ translate('Enter City') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('origin_city') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Region -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Region') }}</label>
                                <input class="form-control {{ $errors->has('origin_region') ? 'is-invalid' : '' }}" name="origin_region" id="origin_region"
                                    type="text" value="{{ old('origin_region', $get_shipping_setting->origin_region) }}"
                                    placeholder="{{ translate('Enter Region') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('origin_region') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Postcode -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Postcode') }}</label>
                                <input class="form-control {{ $errors->has('origin_postcode') ? 'is-invalid' : '' }}" name="origin_postcode" id="origin_postcode"
                                    type="text" value="{{ old('origin_postcode', $get_shipping_setting->origin_postcode) }}"
                                    placeholder="{{ translate('Enter Postcode') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('origin_postcode') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                        <!-- Country -->
                        <div class="col-md-6">
                            <div class="form-group mb-3 ">
                                <label class="col-form-label">{{ translate('Country') }}</label>
                                <input class="form-control {{ $errors->has('origin_country') ? 'is-invalid' : '' }}" name="origin_country" id="origin_country"
                                    type="text" value="{{ old('origin_country', $get_shipping_setting->origin_country ?: 'New Zealand') }}"
                                    placeholder="{{ translate('Enter Country') }}">
                                <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                    <div class="error_msg">@error('origin_country') {{ $message }} @enderror</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-10"></div>
                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-primary">
                                <span class="tf-icons bx bx-save me-1"></span>Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
