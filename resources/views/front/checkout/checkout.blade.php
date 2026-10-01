@extends('front.layout.master')
@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="{{ route('checkout') }}"> Checkout</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="container">
    <?php 
        $customer = Auth::guard('customers')->user();
        $customer_type = $customer->customer_type ?? '';
        /*echo "<pre>";
        print_r($customer->customer_type);
        echo "</pre>";*/
    ?>
</div>

<div class="checkout_section">
	<form method="POST" class="relative_cls" action="{{ route('checkout') }}" enctype="multipart/form-data" id="CheckoutFromID">
		@csrf
	    <div class="container">
            <div id="common_loader">
                <div>
                    <img src="{{ asset('assets/images/loader.gif') }}">
                </div>
            </div>
	        <div class="row">
	            <div class="col-12 col-md-6 col-lg-8">
	                <div class="form-leftcol">

	                	<div id="AddressFromID" >
		                    <!-- Billing Address -->
		                    <div class="billingaddress_form">
		                        <h2>Billing Address</h2> 
		                        <div class="row">

                                    @if( $get_addresses->isNotEmpty() )
                                        <div class="form_row">
                                            <label>Address</label>
                                            <select id="billing_address_id" name="billing_address_id" class="form-control">
                                                <option value="">Select Address</option>
                                                @foreach( $get_addresses as $addr )
                                                    <option value="{{ $addr->id }}">
                                                        {{ $addr->address_title ?? ($addr->first_name . ' ' . $addr->last_name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col-md-12" style="display: none;">
                                        <div class="input_fields" id="Input-billing_address_title">
                                            <label>{{ translate('Address Title') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="billing_address_title" id="billing_address_title" placeholder="{{ translate('Enter your address title') }}" value="Home Address">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>

		                        	<!-- Billing First Name -->
							        <div class="col-md-6">
							            <div class="input_fields"  id="Input-billing_first_name" >
							                <label class="col-form-label">{{ translate('First Name') }} <span class="mandatory cls">*</span></label>
							                <input type="text" class="form-control" name="billing_first_name" id="billing_first_name" placeholder="{{ translate('Enter your first name') }}" value="Dev" >
							                <span class="help-block"></span>
							            </div>                                        
							        </div>     

							        <!-- Billing Last Name -->
							        <div class="col-md-6">
							            <div class="input_fields" id="Input-billing_last_name" >
							                <label class="col-form-label">{{ translate('Last Name') }} <span class="mandatory cls">*</span></label>
							                <input type="text" class="form-control" name="billing_last_name" id="billing_last_name" placeholder="{{ translate('Enter your last name') }}" value="Test"  >
							                <span class="help-block"></span>                                           
							            </div>                                            
							        </div>  

							        <!-- Billing Email -->
							        <div class="col-md-6">
							            <div class="input_fields" id="Input-billing_email" >
							                <label class="col-form-label">{{ translate('Email Address') }} <span class="mandatory cls">*</span></label>
							                <input type="email" class="form-control" name="billing_email" id="billing_email" placeholder="{{ translate('Enter your email address') }}" value="test@gmail.com"  autocomplete="off">
							                <span class="help-block"></span>
							            </div>
							        </div>

							        <!-- Billing Phone Number -->
                                    <div class="col-md-6">
                                        <div class="input_fields"  id="Input-billing_phone" >
                                            <label class="col-form-label">{{ translate('Phone Number') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="billing_phone" id="billing_phone" placeholder="{{ translate('Enter your phone no.') }}" value="9876543110"  >
                                            <span class="help-block"></span>
                                        </div>                                        
                                    </div>

                                    <!-- Billing Company name -->
                                    <div class="col-md-12">
                                        <div class="input_fields"  id="Input-billing_company_name" >
                                            <label class="col-form-label">{{ translate('Company name (optional) ') }} </label>
                                            <input type="text" class="form-control" name="billing_company_name" id="billing_company_name" placeholder="{{ translate('Enter your Company name') }}" value="Test Company"  >
                                            <span class="help-block"></span>
                                        </div>                                        
                                    </div> 

                                    <!-- Country -->
	                                <div class="col-md-6">
	                                    <div class="input_fields" id="Input-billing_country_id" >
                                            <label class="col-form-label">{{ translate('Country') }} 
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <select class="form-control select2 " name="billing_country_id" id="billing_country_id">
                                                <option value="" >Select Country</option>
                                                <?php foreach ($get_countries as $key => $value) { ?>
                                                    <option value="<?php echo $value->id; ?>"  ><?php echo $value->name; ?></option>
                                                <?php } ?>
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
	                                </div>

	                                <!-- State -->
	                                <div class="col-md-6">
	                                    <div class="input_fields" id="Input-billing_state_id" >
                                            <label class="col-form-label">{{ translate('State') }} <span class="mandatory cls">*</span></label>
                                            <select class="form-control select2" name="billing_state_id" id="billing_state_id">
                                                <option value="" >Select State</option>
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
	                                </div>

	                                <!-- City -->
	                                <div class="col-md-6">
                                        <div class="input_fields" id="Input-billing_city_id" >
                                            <label class="col-form-label">
                                                {{ translate('City') }} <span class="mandatory cls">*</span>
                                            </label>
                                            <select class="form-control select2" name="billing_city_id" id="billing_city_id">
                                                <option value="" >Select City</option>
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

	                                <!-- Billing Street address -->
                                    <div class="col-md-12">
                                        <div class="input_fields"  id="Input-billing_street_address" >
                                            <label class="col-form-label">{{ translate('Street address') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="billing_street_address" id="billing_street_address" placeholder="{{ translate('Enter your Street address') }}" value="302, Prakarati Corporate, YN Road"  >
                                            <span class="help-block"></span>
                                        </div>                                        
                                    </div> 

		                        </div>
		                    

		                        <div class="form_bottomrow"> 
                                    <div class="checkbox">
                                        <input type="checkbox" id="sameAddress" name="same_as_shipping_address">
                                        <label for="sameAddress">Same As Shipping Address</label>
                                    </div>

		                            <div class="save-row">
		                                <!-- <span>Save Address For Future</span>
		                                <button class="save-btn" type="button" id="saveAddressBtn">Save</button> -->
		                            </div>
		                        </div>
		                    </div>

		                    <!-- Shipping -->
		                    <div class="billingaddress_form" id="shippingaddress_form">
		                        <h2>Shipping Details</h2>

		                        <div class="row">

                                    @if( $get_addresses->isNotEmpty() )
                                        <div class="form_row">
                                            <label>Address</label>
                                            <select id="shipping_address_id" name="shipping_address_id" class="form-control">
                                                <option value="">Select Address</option>
                                                @foreach( $get_addresses as $addr )
                                                    <option value="{{ $addr->id }}">
                                                        {{ $addr->address_title ?? ($addr->first_name . ' ' . $addr->last_name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="col-md-12" style="display: none;">
                                        <div class="input_fields" id="Input-shipping_address_title">
                                            <label>{{ translate('Address Title') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="shipping_address_title" id="shipping_address_title" placeholder="{{ translate('Enter your address title') }}"  value="Home Address" >
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
		                            <!-- Shipping First Name -->
                                    <div class="col-md-6">
                                        <div class="input_fields"  id="Input-shipping_first_name" >
                                            <label class="col-form-label">{{ translate('First Name') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="shipping_first_name" id="shipping_first_name" placeholder="{{ translate('Enter your first name') }}" value="S Dev"  >
                                            <span class="help-block"></span>
                                        </div>                                        
                                    </div>     

                                    <!-- Shipping Last Name -->
                                    <div class="col-md-6">
                                        <div class="input_fields" id="Input-shipping_last_name" >
                                            <label class="col-form-label">{{ translate('Last Name') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="shipping_last_name" id="shipping_last_name" placeholder="{{ translate('Enter your last name') }}" value="S Test"  >
                                            <span class="help-block"></span>                                           
                                        </div>                                            
                                    </div>  

                                    <!-- Shipping Email -->
                                    <div class="col-md-6">
                                        <div class="input_fields" id="Input-shipping_email" >
                                            <label class="col-form-label">{{ translate('Email Address') }} <span class="mandatory cls">*</span></label>
                                            <input type="email" class="form-control" name="shipping_email" id="shipping_email" placeholder="{{ translate('Enter your email address') }}"  value="shippingdev@gmil.com"  autocomplete="off">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>

                                    <!-- Shipping Phone Number -->
                                    <div class="col-md-6">
                                        <div class="input_fields"  id="Input-shipping_phone" >
                                            <label class="col-form-label">{{ translate('Phone Number') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="shipping_phone" id="shipping_phone" placeholder="{{ translate('Enter your phone no.') }}" value="8770210589"  >
                                            <span class="help-block"></span>
                                        </div>                                        
                                    </div>

                                    <!-- Shipping Company name -->
                                    <div class="col-md-12">
                                        <div class="input_fields"  id="Input-shipping_company_name" >
                                            <label class="col-form-label">{{ translate('Company name (optional) ') }} </label>
                                            <input type="text" class="form-control" name="shipping_company_name" id="shipping_company_name" placeholder="{{ translate('Enter your Company name') }}" value="Shipping Company Test" >
                                            <span class="help-block"></span>
                                        </div>                                        
                                    </div> 

                                    <!-- Country -->
                                    <div class="col-md-6">
                                        <div class="input_fields" id="Input-shipping_country_id" >
                                            <label class="col-form-label">{{ translate('Country') }} 
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <select class="form-control select2 " name="shipping_country_id" id="shipping_country_id">
                                                <option value="" >Select Country</option>
                                                <?php foreach ($get_countries as $key => $value) { ?>
                                                    <option value="<?php echo $value->id; ?>"  ><?php echo $value->name; ?></option>
                                                <?php } ?>
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>

                                    <!-- State -->
                                    <div class="col-md-6">
                                        <div class="input_fields" id="Input-shipping_state_id" >
                                            <label class="col-form-label">{{ translate('State') }} <span class="mandatory cls">*</span></label>
                                            <select class="form-control select2" name="shipping_state_id" id="shipping_state_id">
                                                <option value="" >Select State</option>
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>

                                    <!-- City -->
                                    <div class="col-md-6">
                                        <div class="input_fields" id="Input-shipping_city_id" >
                                            <label class="col-form-label">
                                                {{ translate('City') }} <span class="mandatory cls">*</span>
                                            </label>
                                            <select class="form-control select2" name="shipping_city_id" id="shipping_city_id">
                                                <option value="" >Select City</option>
                                            </select>
                                            <span class="help-block"></span>
                                        </div>
                                    </div>

                                    
                                    <div class="col-md-6">
                                        <div class="input_fields" id="Input-shipping_pincode">
                                            <label>{{ translate('Pin Code') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="shipping_pincode" id="shipping_pincode" placeholder="{{ translate('Enter your Pin Code') }}">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>

                                    <!-- Shipping Street address -->
                                    <div class="col-md-12">
                                        <div class="input_fields"  id="Input-shipping_street_address" >
                                            <label class="col-form-label">{{ translate('Street address') }} <span class="mandatory cls">*</span></label>
                                            <input type="text" class="form-control" name="shipping_street_address" id="shipping_street_address" placeholder="{{ translate('Enter your Street address') }}"  value="Test Shipping Address" >
                                            <span class="help-block"></span>
                                        </div>                                        
                                    </div> 
		                        </div>

		                        <!-- <div class="form_bottomrow">
		                            <div class="save-row shipping_details">
		                                <span>Save Address For Future</span>
		                                <button class="save-btn">Save</button>
		                            </div>
		                        </div> -->
		                    </div>

		                    <!-- Notes -->
		                    <div class="order_notes">
		                        <h2>Order Notes</h2>
		                        <hr>
		                        <div class="notes_textarea">
		                            <label>Order Notes (optional)</label>
		                            <textarea placeholder="Notes about your order..." name="order_note"></textarea>
		                        </div>
		                    </div>
	                    </div>

	                </div>
	            </div>
	            
	            <div class="col-12 col-md-6 col-lg-4">
	                <div class="order_summaryright">
	                    <h2>Order Summary</h2>
	                    <div class="summary_top">

	                        <div class="coupon_box" style="margin-bottom:15px;">
	                            <div class="coupon_input_row" id="couponInputRow" style="display:flex; gap:8px; {{ $total_arr['coupon_code'] ? 'display:none;' : '' }}">
	                                <input type="text" id="coupon_code" class="form-control" placeholder="Enter coupon code" style="text-transform:uppercase;">
	                                <button type="button" id="applyCouponBtn" class="btn btn-primary">Apply</button>
	                            </div>
	                            <div class="coupon_applied_row" id="couponAppliedRow" style="display:{{ $total_arr['coupon_code'] ? 'flex' : 'none' }}; justify-content:space-between; align-items:center;">
	                                <span>Coupon <strong id="appliedCouponCode">{{ $total_arr['coupon_code'] }}</strong> applied</span>
	                                <a href="javascript:void(0);" id="removeCouponBtn" style="color:#e74c3c;">Remove</a>
	                            </div>
	                            <span id="couponMessage" style="font-size:13px; display:none; margin-top:5px;"></span>
	                        </div>

	                        <div class="order_summaryinfo">
	                            <p>Sub total</p>
	                            <p class="price"><span>$</span><span id="summarySubTotal">{{ number_format($total_arr['sub_totals'], 2) }}</span></p>
	                        </div>
	                        <div class="order_summaryinfo" id="discountRow" style="{{ $total_arr['discount'] > 0 ? '' : 'display:none;' }}">
	                            <p>Discount<span id="discountCouponLabel">{{ $total_arr['coupon_code'] ? ' ('.$total_arr['coupon_code'].')' : '' }}</span></p>
	                            <p class="price">-<span>$</span><span id="summaryDiscount">{{ number_format($total_arr['discount'], 2) }}</span></p>
	                        </div>
	                        <div class="order_summaryinfo">
	                            <p>GST (15%)</p>
	                            <p class="price"><span>$</span><span id="summaryGst">{{ number_format($total_arr['gst'], 2) }}</span></p>
	                        </div>
	                        @if(!empty($total_arr['shipping_methods']))
	                            <?php $shippingCount = count($total_arr['shipping_methods']); ?>
	                            <div class="order_summaryinfo" id="shippingRow">
	                                <p>Shipping<?php echo $shippingCount === 1 ? ' (' . e($total_arr['shipping_methods'][0]['title']) . ')' : ''; ?></p>
	                                <p class="price"><span>$</span><span id="summaryShipping">{{ number_format($total_arr['shipping'], 2) }}</span></p>
	                            </div>
	                            @if($shippingCount > 1)
	                                <div class="shipping_methods_box" id="shippingMethodsBox" style="margin:0 0 15px;">
	                                    @foreach($total_arr['shipping_methods'] as $method)
	                                        <div class="shipping_method_option" style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
	                                            <input type="radio"
	                                                   name="shipping_method_choice"
	                                                   class="shipping_method_radio"
	                                                   id="shipping_method_{{ $method['id'] }}"
	                                                   value="{{ $method['id'] }}"
	                                                   {{ (int) $total_arr['selected_shipping_method_id'] === (int) $method['id'] ? 'checked' : '' }}>
	                                            <label for="shipping_method_{{ $method['id'] }}" style="margin:0; font-weight:normal;">
	                                                {{ $method['title'] }} — ${{ number_format($method['cost'], 2) }}
	                                            </label>
	                                        </div>
	                                    @endforeach
	                                </div>
	                            @endif
	                        @endif
	                    </div>
	                    <hr>
	                    <div class="summary_total">
	                        <p class="order_totaltext">Total</p>
	                        <p class="order_totalprice"><span>$</span><span id="summaryTotal">{{ number_format($total_arr['total'], 2) }}</span></p>
	                    </div>
	                    <hr>
	                   
                        <?php if( $customer_type == 'individual' ){ ?>
                            <div class="payment_box">
                                <div class="payment_option">
                                    <label for="payment_type_cod">Cash on delivery</label>
                                    <input type="radio" name="payment_methode" id="payment_type_cod" value="cod">
                                </div>
                                <div class="payment_option">
                                    <label for="payment_type_stripe">Stripe</label>
                                    <input type="radio" name="payment_methode" id="payment_type_stripe" value="stripe">
                                </div>

                                <span id="payment_methode_error" style="color:red; font-size:13px; display:none;">
                                    Please select a payment method.
                                </span>

                                <!-- Stripe Card Element (shown when stripe is selected) -->
                                <div id="stripe_card_section" style="display:none; padding:15px 0;">
                                    <label class="col-form-label">Card Details <span class="mandatory cls">*</span></label>
                                    <div id="card-element" style="border:1px solid #ced4da; padding:10px; border-radius:4px; background:#fff;"></div>
                                    <span id="card-errors" style="color:red; font-size:13px;"></span>
                                </div>
                            </div>
                        <?php } ?>
                        <hr>

	                    <p class="privacy_text">Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our <span>privacy policy.</span> </p>

                        <!-- Hidden field to carry Stripe Payment Intent ID -->
                        <input type="hidden" name="payment_intent_id" id="payment_intent_id" value="">
                        <button type="button" id="placeOrderBtn" class="place_orderbtn">
                            Place Order
                        </button>
	                </div>
	            </div>

	        </div>
	    </div>
    </form>
</div>

@endsection


@section('script')

<!-- Billing Country/State/City Jquery -->
<script type="text/javascript">
    //country_id
    $(document).ready(function () { 
        $(document).on("change", '#billing_country_id', function() {
            biilling_select_states($(this).val());
        });
    });

    //state_id
    $(document).ready(function () { 
        $(document).on("change", '#billing_state_id', function() {
            billing_select_cities($(this).val());
        });
    });

    function biilling_select_states(country_id,child='0'){
        var label_title = "City";
        label_title = "State";
        $.ajax({
            url: "{{ route('front.get_state_by_country_id') }}",
            type: 'post',
            data: {
                'title': label_title,
                'country_id': country_id,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#billing_state_id').html(response);
            }
        });
    }

    function billing_select_cities(state_id,child='0'){
        var label_title = "City";
        var country_id = $('#billing_country_id').val();
        $.ajax({
            url: "{{ route('front.get_city_by_state_id') }}",
            type: 'post',
            data: {
                'title': label_title,
                'country_id': country_id,
                'state_id': state_id,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#billing_city_id').html(response);
            }
        });
    }
</script>

<!-- Shipping Country/State/City Jquery -->
<script type="text/javascript">
    //country_id
    $(document).ready(function () { 
        $(document).on("change", '#shipping_country_id', function() {
            shipping_select_states($(this).val());
        });
    });

    //state_id
    $(document).ready(function () { 
        $(document).on("change", '#shipping_state_id', function() {
            shipping_select_cities($(this).val());
        });
    });

    function shipping_select_states(country_id,child='0'){
        var label_title = "City";
        label_title = "State";
        $.ajax({
            url: "{{ route('front.get_state_by_country_id') }}",
            type: 'post',
            data: {
                'title': label_title,
                'country_id': country_id,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#shipping_state_id').html(response);
            }
        });
    }

    function shipping_select_cities(state_id,child='0'){
        var label_title = "City";
        var country_id = $('#shipping_country_id').val();
        $.ajax({
            url: "{{ route('front.get_city_by_state_id') }}",
            type: 'post',
            data: {
                'title': label_title,
                'country_id': country_id,
                'state_id': state_id,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#shipping_city_id').html(response);
            }
        });
    }
</script>

<!-- Save Address Jquery -->
<script type="text/javascript">
    $(document).on("click", "#saveAddressBtn", function(e) {
	    e.preventDefault();
	    e.stopPropagation(); // prevent triggering parent checkout form

	    // Clear previous errors
	    $('#AddressFromID .input_fields').removeClass('has-error');
	    $('#AddressFromID .help-block').html('');

	    // Manually collect billing fields
	    var formData = new FormData();
	    formData.append('_token', '{{ csrf_token() }}');
        formData.append('billing_address_title',  $('#billing_address_title').val());
	    formData.append('billing_first_name', 	  $('#billing_first_name').val());
	    formData.append('billing_last_name',  	  $('#billing_last_name').val());
	    formData.append('billing_email',      	  $('#billing_email').val());
	    formData.append('billing_phone',      	  $('#billing_phone').val());
	    formData.append('billing_company_name',	  $('#billing_company_name').val());
	    formData.append('billing_country_id',	  $('#billing_country_id').val());
	    formData.append('billing_state_id', 	  $('#billing_state_id').val());
        formData.append('billing_city_id',        $('#billing_city_id').val());
	    formData.append('billing_pincode', 		  $('#billing_pincode').val());
	    formData.append('billing_street_address', $('#billing_street_address').val());

	    $.ajax({
	        type: "POST",
	        url: "{{ route('save-address') }}",
	        dataType: 'JSON',
	        data: formData,
	        cache: false,
	        contentType: false,
	        processData: false,
	        success: function(response) {
	            if (response.error && typeof response.error === 'object') {
	                danger_msg("Something is wrong with required field!");
	                var i = 1;
	                jQuery.each(response.error, function(index, value) {
	                    if (value != '') {
	                        $('#Input-' + index).addClass('has-error');
	                        $('#Input-' + index + ' .help-block').html(value);
	                        if (i == 1) {
	                            $('#' + index).focus();
	                        }
	                        i++;
	                    }
	                });
	            } else {
	                success_msg(response.success);
	            }
	        },
	        error: function(xhr) {
	            danger_msg("Server error. Please try again.");
	        }
	    });
	}); 

    // ── Same as Shipping Address ──────────────────────────────────────────────────
    $('#sameAddress').on('change', function () {
        if ($(this).is(':checked')) {

            // Copy billing → shipping
            $('#shipping_first_name').val($('#billing_first_name').val());
            $('#shipping_last_name').val($('#billing_last_name').val());
            $('#shipping_email').val($('#billing_email').val());
            $('#shipping_phone').val($('#billing_phone').val());
            $('#shipping_company_name').val($('#billing_company_name').val());
            $('#shipping_street_address').val($('#billing_street_address').val());
            $('#shipping_pincode').val($('#billing_pincode').val());
            $('#shipping_address_title').val($('#billing_address_title').val());


            // Copy Country dropdown & trigger state load
            var billingCountry = $('#billing_country_id').val();
            $('#shipping_country_id').val(billingCountry).trigger('change');

            // After state loads, copy state & trigger city load
            // Use a small delay to let the AJAX state load complete
            setTimeout(function () {
                var billingState = $('#billing_state_id').val();
                $('#shipping_state_id').val(billingState).trigger('change');

                setTimeout(function () {
                    var billingCity = $('#billing_city_id').val();
                    $('#shipping_city_id').val(billingCity);
                }, 500);
            }, 500);

            // Lock shipping fields
            $('#shippingaddress_form input, #shippingaddress_form select').prop('readonly', true).css('pointer-events', 'none');
        } else {
            // Unlock & clear shipping fields
            $('#shippingaddress_form input').val('').prop('readonly', false).css('pointer-events', 'auto');
            $('#shippingaddress_form select').val('').prop('readonly', false).css('pointer-events', 'auto');
        }
    });

    // ── If billing fields change while checkbox is checked, sync live ─────────────
    $('#billing_first_name, #billing_last_name, #billing_email, #billing_phone, #billing_company_name, #billing_street_address').on('input', function () {
        if ($('#sameAddress').is(':checked')) {
            var field = $(this).attr('id').replace('billing_', 'shipping_');
            $('#' + field).val($(this).val());
        }
    });
</script>

<script>
    // ── Reusable: fill fields from address response ───────────────────────────
    function fillAddressFields(prefix, data) {
        $('#' + prefix + '_first_name').val(data.first_name);
        $('#' + prefix + '_last_name').val(data.last_name);
        $('#' + prefix + '_email').val(data.email);
        $('#' + prefix + '_phone').val(data.phone);
        $('#' + prefix + '_company_name').val(data.company_name);
        $('#' + prefix + '_street_address').val(data.street_address);
        $('#' + prefix + '_pincode').val(data.pincode);

        // Set country FIRST before any AJAX
        $('#' + prefix + '_country_id').val(data.country).trigger('change.select2'); // trigger for select2 if used

        // Reset state/city first
        $('#' + prefix + '_state_id').html('<option value="">Select State</option>');
        $('#' + prefix + '_city_id').html('<option value="">Select City</option>');

        // Load states → then cities in sequence
        loadStates(prefix, data.country, data.state, function() {
            loadCities(prefix, data.state, data.city);
        });
    }

    function loadStates(prefix, country_id, selected, callback) {
        $.ajax({
            url:  "{{ route('front.get_state_by_country_id') }}",
            type: 'post',
            data: { title: 'State', country_id: country_id, child: selected, _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('#' + prefix + '_state_id').html(response);
                // Force select the correct state after options are rendered
                if (selected && selected != '0') {
                    $('#' + prefix + '_state_id').val(selected);
                }
                if (typeof callback === 'function') callback();
            }
        });
    }

    function loadCities(prefix, state_id, selected) {
        let country_id = $('#' + prefix + '_country_id').val();
        $.ajax({
            url:  "{{ route('front.get_city_by_state_id') }}",
            type: 'post',
            data: { title: 'City', country_id: country_id, state_id: state_id, child: selected, _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('#' + prefix + '_city_id').html(response);
                // Force select the correct city after options are rendered
                if (selected && selected != '0') {
                    $('#' + prefix + '_city_id').val(selected);
                }
            }
        });
    }

    // ── Reusable: fetch address by ID then fill ───────────────────────────────
    function fetchAndFillAddress(address_id, prefix) {
        if (!address_id) return;

        $.ajax({
            url:  "{{ route('get-address-detail') }}",
            type: 'post',
            data: { _token: '{{ csrf_token() }}', address_id: address_id },
            success: function(response) {
                if (!response.error) {
                    fillAddressFields(prefix, response);
                } else {
                    danger_msg(response.message);
                }
            },
            error: function() {
                danger_msg('Failed to load address.');
            }
        });
    }

    // ── Billing dropdown change ───────────────────────────────────────────────
    $(document).on('change', '#billing_address_id', function() {
        fetchAndFillAddress($(this).val(), 'billing');
    });

    // ── Shipping dropdown change ──────────────────────────────────────────────
    $(document).on('change', '#shipping_address_id', function() {
        fetchAndFillAddress($(this).val(), 'shipping');
    });

    // ── Manual country/state change still works ───────────────────────────────
    $(document).on('change', '#billing_country_id',  function() { loadStates('billing', $(this).val(), 0); });
    $(document).on('change', '#billing_state_id',    function() { loadCities('billing', $(this).val(), 0); });
    $(document).on('change', '#shipping_country_id', function() { loadStates('shipping', $(this).val(), 0); });
    $(document).on('change', '#shipping_state_id',   function() { loadCities('shipping', $(this).val(), 0); });
</script>


<?php if( $customer_type == 'individual' ){ ?>
    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    <!-- Stripe Card Element placeholder (shown only when Stripe is selected) -->
    <div id="stripe_card_section" style="display:none; padding: 15px 0;">
        <label class="col-form-label">Card Details <span class="mandatory cls">*</span></label>
        <div id="card-element" style="border:1px solid #ced4da; padding:10px; border-radius:4px; background:#fff;"></div>
        <span id="card-errors" style="color:red; font-size:13px;"></span>
    </div>
<?php } ?>
<script>

    <?php if( $customer_type == 'individual' ){ ?>

        // ── Stripe Setup ──────────────────────────────────────────────────────────
        var stripe        = Stripe('{{ config("services.stripe.key") }}');
        var elements      = stripe.elements();
        var cardElement   = elements.create('card');
        var clientSecret  = null;
        var paymentIntentId = null;

        cardElement.mount('#card-element');
        cardElement.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            displayError.textContent = event.error ? event.error.message : '';
        });

        // ── Show/hide Stripe card section on payment method change ────────────────
        $('input[name="payment_methode"]').on('change', function() {
            if ($(this).val() == 'stripe') {
                $('#stripe_card_section').show();
                createPaymentIntent(); // create intent when stripe is selected
            } else {
                $('#stripe_card_section').hide();
                clientSecret    = null;
                paymentIntentId = null;
                $('#payment_intent_id').val('');
            }
        });

        // ── Create Payment Intent via AJAX ────────────────────────────────────────
        function createPaymentIntent() {
            // Read the live total (not the page-load value) so a coupon applied/removed
            // after Stripe was selected still charges the correct, up-to-date amount.
            var totalAmount = Math.round(parseFloat($('#summaryTotal').text()) * 100); // convert to cents

            $.ajax({
                type: 'POST',
                url: '{{ route("create.payment.intent") }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    amount: totalAmount,
                },
                success: function(response) {
                    if (response.success) {
                        clientSecret = response.clientSecret;
                        // extract payment intent id from clientSecret
                        paymentIntentId = clientSecret.split('_secret_')[0];
                    } else {
                        danger_msg('Could not initialize payment. Please try again.');
                    }
                },
                error: function() {
                    danger_msg('Server error. Please refresh and try again.');
                }
            });
        }

    <?php } ?>

    // ── Coupon apply/remove & shipping-method selection ─────────────────────────
    // Shared renderer: applyCoupon/removeCoupon/selectShipping all return the
    // same shape (sub_total/discount/shipping/gst/total[/code]), so any of them
    // can refresh the whole summary block consistently.
    function renderCouponTotals(response) {
        $('#summarySubTotal').text(parseFloat(response.sub_total).toFixed(2));
        $('#summaryDiscount').text(parseFloat(response.discount).toFixed(2));
        if (response.shipping !== undefined && $('#summaryShipping').length) {
            $('#summaryShipping').text(parseFloat(response.shipping).toFixed(2));
        }
        $('#summaryGst').text(parseFloat(response.gst).toFixed(2));
        $('#summaryTotal').text(parseFloat(response.total).toFixed(2));
        if (response.discount > 0) {
            $('#discountCouponLabel').text(response.code ? ' (' + response.code + ')' : '');
            $('#discountRow').show();
        } else {
            $('#discountRow').hide();
        }

        // The Stripe PaymentIntent was created for the pre-coupon-change total - if
        // Stripe is the selected method, refresh it so the card is charged the
        // updated (discounted) amount instead of the stale one.
        if (typeof createPaymentIntent === 'function' &&
            $('input[name="payment_methode"]:checked').val() === 'stripe') {
            createPaymentIntent();
        }
    }

    $(document).on('click', '#applyCouponBtn', function(e) {
        e.preventDefault();
        var code = $('#coupon_code').val().trim();
        $('#couponMessage').hide();
        if (!code) {
            $('#couponMessage').css('color', 'red').text('Please enter a coupon code.').show();
            return;
        }
        $.ajax({
            type: 'POST',
            url: '{{ route("apply-coupon") }}',
            data: { code: code, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    renderCouponTotals(response);
                    $('#appliedCouponCode').text(response.code);
                    $('#couponInputRow').hide();
                    $('#couponAppliedRow').css('display', 'flex');
                    $('#couponMessage').hide();
                    success_msg(response.message);
                } else {
                    $('#couponMessage').css('color', 'red').text(response.message).show();
                    danger_msg(response.message);
                }
            },
            error: function() {
                danger_msg('Server error. Please try again.');
            }
        });
    });

    $(document).on('click', '#removeCouponBtn', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: '{{ route("remove-coupon") }}',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                renderCouponTotals(response);
                $('#coupon_code').val('');
                $('#couponInputRow').css('display', 'flex');
                $('#couponAppliedRow').hide();
                $('#couponMessage').hide();
            },
            error: function() {
                danger_msg('Server error. Please try again.');
            }
        });
    });

    // ── Shipping method selection ───────────────────────────────────────────────
    $(document).on('change', '.shipping_method_radio', function() {
        var methodId = $(this).val();
        $.ajax({
            type: 'POST',
            url: '{{ route("checkout.select-shipping") }}',
            data: { shipping_method_id: methodId, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    renderCouponTotals(response);
                } else {
                    danger_msg(response.message || 'Could not update shipping method.');
                }
            },
            error: function() {
                danger_msg('Server error. Please try again.');
            }
        });
    });

    // ── Place Order button click ───────────────────────────────────────────────
    $(document).on('click', '#placeOrderBtn', function(e) {
        e.preventDefault();

        <?php if( $customer_type == 'individual' ){ ?>
            var paymentMethod = $('input[name="payment_methode"]:checked').val();

            if (paymentMethod == 'stripe') {
                // Confirm card payment with Stripe first
                stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: $('#billing_first_name').val() + ' ' + $('#billing_last_name').val(),
                            email: $('#billing_email').val(),
                            phone: $('#billing_phone').val(),
                        }
                    }
                }).then(function(result) {
                    if (result.error) {
                        $('#card-errors').text(result.error.message);
                        danger_msg(result.error.message);
                    } else if (result.paymentIntent.status === 'succeeded') {
                        // Payment confirmed — now submit order to Laravel
                        $('#payment_intent_id').val(result.paymentIntent.id);
                        submitCheckoutForm();
                    }
                });
            } else {
                // COD — submit directly
                submitCheckoutForm();
            }
        <?php }else{ ?>
            submitCheckoutForm();
        <?php } ?>
    });

    // ── Submit checkout form via AJAX ─────────────────────────────────────────
    function submitCheckoutForm() {
       
        $('#CheckoutFromID .input_fields').removeClass('has-error');
        $('#CheckoutFromID .help-block').html('');

        var sameAddress = $('#sameAddress').is(':checked');
        var hasError    = false;

        <?php if( $customer_type == 'individual' ){ ?>
            $('#payment_methode_error').hide();  // reset payment error
            var paymentMethod = $('input[name="payment_methode"]:checked').val();
        <?php } ?>

        // ── Client-side billing validation ────────────────────────────────────────
        var billingFields = [
            { id: 'billing_first_name',     label: 'First Name' },
            { id: 'billing_last_name',      label: 'Last Name' },
            { id: 'billing_email',          label: 'Email Address' },
            { id: 'billing_phone',          label: 'Phone Number' },
            { id: 'billing_country_id',     label: 'Country' },
            { id: 'billing_state_id',       label: 'State' },
            { id: 'billing_city_id',        label: 'City' },
            { id: 'billing_street_address', label: 'Street Address' },
        ];

        billingFields.forEach(function(field) {
            if ($('#' + field.id).val() == '' || $('#' + field.id).val() == null) {
                $('#Input-' + field.id).addClass('has-error');
                $('#Input-' + field.id + ' .help-block').html(field.label + ' is required.');
                hasError = true;
            }
        });

        // ── Client-side shipping validation (only if checkbox NOT checked) ────────
        if (!sameAddress) {
            var shippingFields = [
                { id: 'shipping_first_name',     label: 'First Name' },
                { id: 'shipping_last_name',      label: 'Last Name' },
                { id: 'shipping_email',          label: 'Email Address' },
                { id: 'shipping_phone',          label: 'Phone Number' },
                { id: 'shipping_country_id',     label: 'Country' },
                { id: 'shipping_state_id',       label: 'State' },
                { id: 'shipping_city_id',        label: 'City' },
                { id: 'shipping_street_address', label: 'Street Address' },
                { id: 'shipping_pincode',        label: 'Pin Code' },

            ];

            shippingFields.forEach(function(field) {
                if ($('#' + field.id).val() == '' || $('#' + field.id).val() == null) {
                    $('#Input-' + field.id).addClass('has-error');
                    $('#Input-' + field.id + ' .help-block').html(field.label + ' is required.');
                    hasError = true;
                }
            });
        }

        <?php if( $customer_type == 'individual' ){ ?>
            if (!paymentMethod) {
                $('#payment_methode_error').show();
                hasError = true;
            }
        <?php } ?>

        if (hasError) {
            //$('#CheckoutFromID #common_loader').show();
            danger_msg('Please fill in all required fields.');
            // Scroll to first error
            /*$('html, body').animate({
                scrollTop: $('.has-error:first').offset().top - 100
            }, 500);*/
            setTimeout(function(){
                //$('#CheckoutFromID #common_loader').hide();
            },500);
            return false;
        }

        $('#shippingaddress_form input, #shippingaddress_form select').prop('readonly', false).css('pointer-events', 'auto');

        // ── Proceed with AJAX ─────────────────────────────────────────────────────
        var formData = new FormData(document.getElementById('CheckoutFromID'));

        $.ajax({
            type: 'POST',
            url: '{{ route("place_order") }}',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function(result){
               jQuery('#CheckoutFromID #common_loader').show();
            },
            success: function(response) {
                jQuery('#CheckoutFromID #common_loader').hide();
                if (response.error && typeof response.error === 'object') {
                    danger_msg('Please fix the errors below.');
                    var i = 1;
                    $.each(response.error, function(index, value) {
                        if (value != '') {
                            $('#Input-' + index).addClass('has-error');
                            $('#Input-' + index + ' .help-block').html(value[0]);
                            if (i == 1) {
                                $('#Input-'+index).focus();
                            }
                            i++;
                        }
                    });
                } else {
                    success_msg(response.success);
                    setTimeout(function () {
                        window.location.href = '{{ route("user.dashboard") }}';
                    }, 2000);
                }
            },
            error: function(xhr) {
                danger_msg('Server error. Please try again.');
            }
        });
    }
</script>

@endsection