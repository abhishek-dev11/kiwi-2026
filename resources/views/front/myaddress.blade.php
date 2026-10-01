@extends('front.layout.master')
@section('content')

    <div class="breadcrumb_sec">
        <div class="container">
            <div class="breadcrumb_row">
                <ul>
                    <li><a href="{{ route('user.dashboard') }}">My Account</a></li>
                    <li><span>/</span></li>
                    <li class="active"><a href="{{ route('user.address') }}">My Addresses</a></li>
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
                    <div class="order_rightcol address_rightcol">
                        <div class="top_addressrow">
                            <h2>My Addresses</h2>
                            <div class="submit_btn orange_btn">
                                <button type="button" id="addNewAddressBtn" data-bs-toggle="modal" data-bs-target="#addressModal">
                                    Add New Address
                                </button>
                            </div>
                        </div>

                        {{-- ADDRESS LIST --}}
                        <div id="addressList">
                            @forelse($address_arrs as $addr)
                                <div class="address_innerrow" id="address_row_{{ $addr['id'] }}">
                                    <div class="address_left">
                                        <div class="deleteaddress">
                                            <a href="#" class="DeleteAddress" data-id="{{ $addr['id'] }}">
                                                <img src="{{ asset('assets/dist/images/delete.svg') }}">
                                            </a>
                                        </div>
                                        <div class="home_address">
                                            <h6>{{ $addr['address_title'] ?? 'Home' }}</h6>
                                            <div class="person_info">
                                                <p>{{ $addr['first_name'] }} {{ $addr['last_name'] }}</p>
                                                <p>{{ $addr['phone'] }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="address_right">
                                        <div class="address_righttop">
                                            <a class="edit_btn EditAddress" href="#"
                                               data-bs-toggle="modal"
                                               data-bs-target="#addressModal"
                                               data-id="{{ $addr['id'] }}"
                                               data-address_title="{{ $addr['address_title'] }}"
                                               data-first_name="{{ $addr['first_name'] }}"
                                               data-last_name="{{ $addr['last_name'] }}"
                                               data-email="{{ $addr['email'] }}"
                                               data-phone="{{ $addr['phone'] }}"
                                               data-company="{{ $addr['company_name'] }}"
                                               data-country="{{ $addr['country'] }}"
                                               data-state="{{ $addr['state'] }}"
                                               data-city="{{ $addr['city'] }}"
                                               data-pincode="{{ $addr['pincode'] }}"
                                               data-street="{{ $addr['street_address'] }}">
                                                <img src="{{ asset('assets/dist/images/edit.svg') }}">Edit
                                            </a>
                                            <p>Default Address</p>
                                            <label class="switch">
                                                <input type="checkbox"
                                                       class="default-switch"
                                                       data-id="{{ $addr['id'] }}"
                                                       {{ $addr['is_default'] ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                        <div class="address_rightbottom">
                                            <div>
                                                <p>Address</p>
                                                <span>{{ $addr['street_address'] }}</span>
                                            </div>
                                            <div>
                                                <p>City</p>
                                                <span>{{ $addr['city_name'] }}</span>
                                            </div>
                                            <div>
                                                <p>State</p>
                                                <span>{{ $addr['state_name'] }}</span>
                                            </div>
                                            <div>
                                                <p>Pincode</p>
                                                <span>{{ $addr['pincode'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div id="noAddress">
                                    <p>No addresses found. Add your first address.</p>
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ADD / EDIT ADDRESS MODAL --}}
    <div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered saveaddress-popup">
            <div class="modal-content p-3" style="border-radius:15px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="addressModalTitle">Add New Address</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="contact_form saveaddressform">
                        <div id="AddressFromID">

                            {{-- Hidden: address_id = 0 for new, filled for edit --}}
                            <input type="hidden" name="address_id" id="address_id" value="0">

                            <div class="row">

                                <div class="col-md-12">
                                    <div class="input_fields" id="Input-billing_address_title">
                                        <label>{{ translate('Address Title') }} <span class="mandatory cls">*</span></label>
                                        <input type="text" class="form-control" name="billing_address_title" id="billing_address_title" placeholder="{{ translate('Enter your address title') }}">
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input_fields" id="Input-billing_first_name">
                                        <label>{{ translate('First Name') }} <span class="mandatory cls">*</span></label>
                                        <input type="text" class="form-control" name="billing_first_name" id="billing_first_name" placeholder="{{ translate('Enter your first name') }}">
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input_fields" id="Input-billing_last_name">
                                        <label>{{ translate('Last Name') }} <span class="mandatory cls">*</span></label>
                                        <input type="text" class="form-control" name="billing_last_name" id="billing_last_name" placeholder="{{ translate('Enter your last name') }}">
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input_fields" id="Input-billing_email">
                                        <label>{{ translate('Email Address') }} <span class="mandatory cls">*</span></label>
                                        <input type="email" class="form-control" name="billing_email" id="billing_email" placeholder="{{ translate('Enter your email address') }}" autocomplete="off">
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input_fields" id="Input-billing_phone">
                                        <label>{{ translate('Phone Number') }} <span class="mandatory cls">*</span></label>
                                        <input type="text" class="form-control" name="billing_phone" id="billing_phone" placeholder="{{ translate('Enter your phone no.') }}">
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input_fields" id="Input-billing_country_id">
                                        <label>{{ translate('Country') }} <span class="mandatory cls">*</span></label>
                                        <select class="form-control" name="billing_country_id" id="billing_country_id">
                                            <option value="">Select Country</option>
                                            @foreach($get_countries as $country)
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input_fields" id="Input-billing_state_id">
                                        <label>{{ translate('State') }} <span class="mandatory cls">*</span></label>
                                        <select class="form-control" name="billing_state_id" id="billing_state_id">
                                            <option value="">Select State</option>
                                        </select>
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input_fields" id="Input-billing_city_id">
                                        <label>{{ translate('City') }} <span class="mandatory cls">*</span></label>
                                        <select class="form-control" name="billing_city_id" id="billing_city_id">
                                            <option value="">Select City</option>
                                        </select>
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input_fields" id="Input-billing_pincode">
                                        <label>{{ translate('Pin Code') }} <span class="mandatory cls">*</span></label>
                                        <input type="text" class="form-control" name="billing_pincode" id="billing_pincode" placeholder="{{ translate('Enter your Pin Code') }}">
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="input_fields" id="Input-billing_street_address">
                                        <label>{{ translate('Street address') }} <span class="mandatory cls">*</span></label>
                                        <input type="text" class="form-control" name="billing_street_address" id="billing_street_address" placeholder="{{ translate('Enter your Street address') }}">
                                        <span class="help-block"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="save_addressbtn">
                                <div class="cancel_btn">
                                    <button type="button" data-bs-dismiss="modal">Cancel</button>
                                </div>
                                <div class="create_accountbottom orange_btn">
                                    <button class="save-btn" type="button" id="saveAddressBtn">Save Address</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        // ------------------------------------------------
        // RESET MODAL FOR ADD NEW
        // ------------------------------------------------
        $('#addNewAddressBtn').on('click', function() {
            $('#addressModalTitle').text('Add New Address');
            $('#address_id').val('0');
            $('#AddressFromID input:not([name="_token"])').val('');
            $('#billing_state_id').html('<option value="">Select State</option>');
            $('#billing_city_id').html('<option value="">Select City</option>');
            $('#billing_country_id').val('');
            $('#AddressFromID .input_fields').removeClass('has-error');
            $('#AddressFromID .help-block').html('');
        });

        // ------------------------------------------------
        // FILL MODAL FOR EDIT
        // ------------------------------------------------
        $(document).on('click', '.EditAddress', function() {
            let btn = $(this);
            $('#addressModalTitle').text('Edit Address');

            $('#address_id').val(btn.data('id'));
            $('#billing_address_title').val(btn.data('address_title'));
            $('#billing_first_name').val(btn.data('first_name'));
            $('#billing_last_name').val(btn.data('last_name'));
            $('#billing_email').val(btn.data('email'));
            $('#billing_phone').val(btn.data('phone'));
            $('#billing_pincode').val(btn.data('pincode'));
            $('#billing_street_address').val(btn.data('street'));

            $('#AddressFromID .input_fields').removeClass('has-error');
            $('#AddressFromID .help-block').html('');

            // Load state then city in sequence
            let country_id = btn.data('country');
            let state_id   = btn.data('state');
            let city_id    = btn.data('city');

            $('#billing_country_id').val(country_id);

            biilling_select_states(country_id, state_id, function() {
                billing_select_cities(state_id, city_id);
            });
        });

        // ------------------------------------------------
        // SAVE / UPDATE ADDRESS
        // ------------------------------------------------
        $(document).on('click', '#saveAddressBtn', function(e) {
            e.preventDefault();

            $('#AddressFromID .input_fields').removeClass('has-error');
            $('#AddressFromID .help-block').html('');

            let address_id = $('#address_id').val();
            let isEdit     = address_id != '0' && address_id != '';
            let url        = isEdit ? "{{ route('update-address') }}" : "{{ route('save-address') }}";

            var formData = new FormData();
            formData.append('_token',                 '{{ csrf_token() }}');
            formData.append('address_id',             address_id);
            formData.append('billing_address_title',  $('#billing_address_title').val());
            formData.append('billing_first_name',     $('#billing_first_name').val());
            formData.append('billing_last_name',      $('#billing_last_name').val());
            formData.append('billing_email',          $('#billing_email').val());
            formData.append('billing_phone',          $('#billing_phone').val());
            formData.append('billing_country_id',     $('#billing_country_id').val());
            formData.append('billing_state_id',       $('#billing_state_id').val());
            formData.append('billing_city_id',        $('#billing_city_id').val());
            formData.append('billing_pincode',        $('#billing_pincode').val());
            formData.append('billing_street_address', $('#billing_street_address').val());

            $.ajax({
                type: 'POST',
                url:  url,
                dataType: 'JSON',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.error && typeof response.error === 'object') {
                        danger_msg('Something is wrong with required field!');
                        $.each(response.error, function(index, value) {
                            if (value != '') {
                                $('#Input-' + index).addClass('has-error');
                                $('#Input-' + index + ' .help-block').html(value);
                            }
                        });
                    } else {
                        success_msg(response.success);
                        $('#addressModal').modal('hide');
                        setTimeout(function() { window.location.reload(); }, 1000);
                    }
                },
                error: function() {
                    danger_msg('Server error. Please try again.');
                }
            });
        });

        // ------------------------------------------------
        // DELETE ADDRESS
        // ------------------------------------------------
        $(document).on('click', '.DeleteAddress', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            if (!confirm('Are you sure you want to delete this address?')) return;

            $.ajax({
                type: 'POST',
                url:  "{{ route('delete-address') }}",
                data: { _token: '{{ csrf_token() }}', address_id: id },
                success: function(response) {
                    if (!response.error) {
                        $('#address_row_' + id).fadeOut(300, function() {
                            $(this).remove();
                            if ($('.address_innerrow').length === 0) {
                                $('#addressList').html('<p>No addresses found. Add your first address.</p>');
                            }
                        });
                        success_msg(response.message);
                    }
                },
                error: function() { danger_msg('Server error.'); }
            });
        });

        // ------------------------------------------------
        // DEFAULT ADDRESS TOGGLE
        // ------------------------------------------------
        $(document).on('change', '.default-switch', function() {
            let id        = $(this).data('id');
            let isChecked = $(this).is(':checked');

            if (!isChecked) {
                // Prevent unchecking — must always have a default
                $(this).prop('checked', true);
                return;
            }

            // Uncheck all other toggles visually
            $('.default-switch').not(this).prop('checked', false);

            $.ajax({
                type: 'POST',
                url:  "{{ route('set-default-address') }}",
                data: { _token: '{{ csrf_token() }}', address_id: id },
                success: function(response) {
                    if (!response.error) {
                        success_msg(response.message);
                    }
                },
                error: function() { danger_msg('Server error.'); }
            });
        });

        // ------------------------------------------------
        // COUNTRY / STATE / CITY (updated with callback for edit)
        // ------------------------------------------------
        $(document).on('change', '#billing_country_id', function() {
            biilling_select_states($(this).val());
        });

        $(document).on('change', '#billing_state_id', function() {
            billing_select_cities($(this).val());
        });

        function biilling_select_states(country_id, selected = '0', callback = null) {
            $.ajax({
                url:  "{{ route('front.get_state_by_country_id') }}",
                type: 'post',
                data: { title: 'State', country_id: country_id, child: selected, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    $('#billing_state_id').html(response);
                    if (selected != '0') $('#billing_state_id').val(selected);
                    if (typeof callback === 'function') callback();
                }
            });
        }

        function billing_select_cities(state_id, selected = '0') {
            let country_id = $('#billing_country_id').val();
            $.ajax({
                url:  "{{ route('front.get_city_by_state_id') }}",
                type: 'post',
                data: { title: 'City', country_id: country_id, state_id: state_id, child: selected, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    $('#billing_city_id').html(response);
                    if (selected != '0') $('#billing_city_id').val(selected);
                }
            });
        }
    </script>
@endsection