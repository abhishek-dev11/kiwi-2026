<form method="POST" class="relative_cls" action="{{ route('front.trade_signup') }}" enctype="multipart/form-data" id="TradeFromID">
    @csrf
    <div class="row">
        <div class="col">
            <div class="nav-align-top mb-3">

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link general_info_cls <?php if($errors->has('tab')){ if($errors->first('tab')=='GeneralInformation'){ ?> active <?php } }else{ ?> active <?php } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-GeneralInformation" role="tab" aria-selected="true">
                            <div class="step-content">
                                <div class="step-circle">01</div>
                                <p class="step-label">Basic & Personal Info</p>
                            </div>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link companydetails_cls <?php if($errors->has('tab')){ if($errors->first('tab')=='companydetails'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-companydetails" role="tab" aria-selected="false">
                            <div class="step-content">
                                <div class="step-circle">02</div>
                                <p class="step-label">Company Name</p>
                            </div>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link address_tax_cls <?php if($errors->has('tab')){ if($errors->first('tab')=='address_tax'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-address_tax" role="tab" aria-selected="false">
                            <div class="step-content">
                                <div class="step-circle">03</div>
                                <p class="step-label">Address & Tax Details</p>
                            </div>
                        </button>
                    </li>

                    <li class="nav-item">
                        <button type="button" class="nav-link delivery_info_cls <?php if($errors->has('tab')){ if($errors->first('tab')=='delivery_info'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-delivery_info" role="tab" aria-selected="false">
                            <div class="step-content">
                                <div class="step-circle">04</div>
                                <p class="step-label">Delivery & Trade Info</p>
                            </div>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- Basic & Personal Info -->
                    <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='GeneralInformation'){ ?> active show <?php } }else{ ?> active show <?php } ?>" id="form-tabs-GeneralInformation" role="tabpanel">
                        <div class="form-step">
                            <h3>Basic & Personal Info</h3>
                            <div class="row">

                                <!-- First Name -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('First Name') }} <span class="mandatory cls">*</span></label>
                                            <input class="form-control {{ $errors->has('trade_first_name') ? 'is-invalid' : '' }}" name="trade_first_name" id="trade_first_name" type="text"  placeholder="{{ translate('Enter First Name') }}" value="" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('trade_first_name') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div>     

                                <!-- Last Name -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('Last Name') }} <span class="mandatory cls">*</span></label>
                                            <input class="form-control {{ $errors->has('trade_last_name') ? 'is-invalid' : '' }}" name="trade_last_name" id="trade_last_name"  type="text"  placeholder="{{ translate('Enter Last Name') }}"  value="" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('trade_last_name') {{ $message }} @enderror
                                                </div>
                                            </div>                                       
                                        </div>                                        
                                    </div>
                                </div>  

                                <!-- Phone Number -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('Phone Number') }} <span class="mandatory cls">*</span></label>
                                            <input class="form-control {{ $errors->has('trade_phone') ? 'is-invalid' : '' }}" name="trade_phone" id="trade_phone" type="text"  placeholder="{{ translate('Enter Phone Number') }}"  value="">
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('trade_phone') {{ $message }} @enderror</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('Email') }} <span class="mandatory cls">*</span></label>
                                            <input class="form-control {{ $errors->has('trade_email') ? 'is-invalid' : '' }}" name="trade_email" id="trade_email"
                                                type="text"  
                                                placeholder="{{ translate('Enter Email') }}" autocomplete="off">
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('trade_email') {{ $message }} @enderror</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('Password') }} <span class="mandatory cls">*</span></label>
                                            <input class="form-control {{ $errors->has('trade_password') ? 'is-invalid' : '' }}" name="trade_password" id="trade_password"  type="password"  placeholder="{{ translate('Enter Password') }}">
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('trade_password') {{ $message }} @enderror</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('Confirm Password') }} <span class="mandatory cls">*</span></label>
                                            <input type="password" class="form-control {{ $errors->has('trade_confirm_password') ? 'is-invalid' : '' }}" name="trade_confirm_password" id="trade_confirm_password"
                                                type="text" value="" 
                                                placeholder="{{ translate('Enter Confirm Password') }}">
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('trade_confirm_password') {{ $message }} @enderror</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact_form">
                                <div class="create_accountbottom orange_btn">
                                    <div class="setnew_account">
                                        <p>Already have  an account? <a href="{{ route('user.login') }}"> Login</a></p>
                                    </div>
                                    <button type="button" class="next">Next</button>
                                </div>  
                            </div>
                        </div>
                    </div>

                    <!-- Company Basic Info -->
                    <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='companydetails'){ ?> active show <?php } } ?>" id="form-tabs-companydetails" role="tabpanel">
                        <div class="form-step">
                            <h3>Company Basic Info</h3>
                            <div class="row">

                                <!-- Company Name -->
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('Company Name') }} 
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_registered_name') ? 'is-invalid' : '' }}" name="company_registered_name" id="company_registered_name"
                                                type="text" 
                                                placeholder="{{ translate('Enter your company registered name') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_registered_name') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div>  

                                <!-- Company Trading Name -->
                                <div class="col-md-6">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Company Trading Name') }} ( {{ translate('If applicable') }} )
                                            </label>
                                            <input class="form-control {{ $errors->has('company_trading_name') ? 'is-invalid' : '' }}" name="company_trading_name" id="company_trading_name"
                                                type="text" 
                                                placeholder="{{ translate('Enter your company registered name') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_trading_name') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div>

                                <!-- Company Registration Number or NZBN -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('Company Registration Number or NZBN') }} 
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_registration_number') ? 'is-invalid' : '' }}" name="company_registration_number" id="company_registration_number"
                                                type="text" 
                                                placeholder="{{ translate('Enter your company registration number') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_registration_number') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Period of Trading -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Period of Trading') }}  ( {{ translate('In years') }} )
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('period_of_trading') ? 'is-invalid' : '' }}" name="period_of_trading" id="period_of_trading"
                                                type="text" 
                                                placeholder="{{ translate('Enter your Period of Trading') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('period_of_trading') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Number of staff -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Number of staff') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('number_of_staff') ? 'is-invalid' : '' }}" name="number_of_staff" id="number_of_staff"
                                                type="number" 
                                                placeholder="{{ translate('Enter your Number of staff') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('number_of_staff') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 
                            </div>

                            <div class="contact_form">
                                <div class="create_accountbottom orange_btn responisve_mobile">
                                    <button type="button" class="prev" data-target=".general_info_cls">Previous</button>
                                    <button type="button" class="next">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address & Tax Details -->
                    <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='address_tax'){ ?> active show <?php } } ?>" id="form-tabs-address_tax" role="tabpanel">
                        <div class="form-step">
                            <h3>Address & Tax Details</h3>
                            <div class="row">

                                <!-- Postal / Physical Address Details -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Postal / Physical Address Details') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('physical_address') ? 'is-invalid' : '' }}" name="physical_address" id="physical_address" type="text" placeholder="{{ translate('Enter your Postal / Physical Address Details') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('physical_address') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Address Line 2 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label"> {{ translate('Address Line 2') }} </label>
                                            <input class="form-control {{ $errors->has('address_line_2') ? 'is-invalid' : '' }}" name="address_line_2" id="address_line_2" type="text"  placeholder="{{ translate('Enter your Address Line 2') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('address_line_2') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Country -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('Country') }} 
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <select class="form-control select2 {{ $errors->has('country_id') ? 'is-invalid' : '' }}" name="country_id" id="country_id">
                                                <option value="" >Select Country</option>
                                                <?php foreach ($get_countries as $key => $value) { ?>
                                                    <option value="<?php echo $value->id; ?>"  ><?php echo $value->name; ?></option>
                                                <?php } ?>
                                            </select>
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationcity_id" data-validator="notEmpty" class="error_msg">@error('city_id') {{ $message }} @enderror </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- State -->
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="input_fields">
                                            <label class="col-form-label">{{ translate('State') }} <span class="mandatory cls">*</span></label>
                                            <select class="form-control select2 {{ $errors->has('state_id') ? 'is-invalid' : '' }}" name="state_id" id="state_id">
                                                <option value="" >Select State</option>
                                            </select>
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationcity_id" data-validator="notEmpty" class="error_msg">@error('city_id') {{ $message }} @enderror </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- City -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('City') }} <span class="mandatory cls">*</span>
                                            </label>
                                            <select class="form-control select2 {{ $errors->has('city_id') ? 'is-invalid' : '' }}" name="city_id" id="city_id">
                                                <option value="" >Select City</option>
                                            </select>
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationcity_id" data-validator="notEmpty" class="error_msg">@error('city_id') {{ $message }} @enderror </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Postcode / ZIP -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Postcode / ZIP') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('postcode') ? 'is-invalid' : '' }}" name="postcode" id="postcode" type="text" placeholder="{{ translate('Enter your Postcode / ZIP') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('postcode') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- GST -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label"> {{ translate('GST') }} </label>
                                            <input class="form-control {{ $errors->has('gst_number') ? 'is-invalid' : '' }}" name="gst_number" id="gst_number" type="text" placeholder="{{ translate('Enter your GST') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('gst_number') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 
                            </div>

                            <div class="contact_form">
                                <div class="create_accountbottom orange_btn responisve_mobile">
                                    <button type="button" class="prev" data-target=".companydetails_cls">Previous</button>
                                    <button type="button" class="next">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery & Trade Information -->
                    <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='delivery_info'){ ?> active show <?php } } ?>" id="form-tabs-delivery_info" role="tabpanel">
                        <div class="form-step">
                            <h3>Delivery & Trade Information</h3>
                            <div class="row">
                                <!-- Delivery Contact First name -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label"> {{ translate('Delivery Contact First name') }}</label>
                                            <input class="form-control {{ $errors->has('delivery_first_name') ? 'is-invalid' : '' }}" name="delivery_first_name" id="delivery_first_name" type="text" placeholder="{{ translate('Enter your delivery contact first name') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('delivery_first_name') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Delivery Contact Last Name -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label"> {{ translate('Delivery Contact Last Name') }}</label>
                                            <input class="form-control {{ $errors->has('delivery_last_name') ? 'is-invalid' : '' }}" name="delivery_last_name" id="delivery_last_name" type="text" placeholder="{{ translate('Enter your Delivery Contact Last Name') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('delivery_last_name') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Delivery Contact Phone -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label"> {{ translate('Delivery Contact Phone') }}</label>
                                            <input class="form-control {{ $errors->has('delivery_phone') ? 'is-invalid' : '' }}" name="delivery_phone" id="delivery_phone" type="text" placeholder="{{ translate('Enter your Delivery Contact Phone') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('delivery_phone') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Delivery Address Details -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label"> {{ translate('Delivery Address Details') }} </label>
                                            <input class="form-control {{ $errors->has('delivery_address') ? 'is-invalid' : '' }}" name="delivery_address" id="delivery_address" type="text" placeholder="{{ translate('Enter your Delivery Address Details') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('delivery_address') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Customer Address Detail -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Customer Address Detail') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('customer_address') ? 'is-invalid' : '' }}" name="customer_address" id="customer_address" type="text" placeholder="{{ translate('Enter your Customer Address Detail') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('customer_address') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h3 style="font-size: 14px;">Trade Information. Please fill out the following information:</h3>
                                </div>
                            </div>

                            <!-- Company Name 1 -->
                            <div class="row">
                                <!-- Company Name 1 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                1. {{ translate('Company Name') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_name_2') ? 'is-invalid' : '' }}" name="company_name_2" id="company_name_2" type="text" placeholder="{{ translate('Enter your Company Name') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_name_2') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Address 1 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Address') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_address_1') ? 'is-invalid' : '' }}" name="company_address_1" id="company_address_1" type="text" placeholder="{{ translate('Enter address for company 1') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_address_1') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Telephone No 1 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Telephone No') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_tel_1') ? 'is-invalid' : '' }}" name="company_tel_1" id="company_tel_1" type="text" placeholder="{{ translate('Telephone No for Company 1') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_tel_1') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Account # or name 1 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Account # or name') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('account_name_1') ? 'is-invalid' : '' }}" name="account_name_1" id="account_name_1" type="text" placeholder="{{ translate('exactly as it appears on acct') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('account_name_1') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 
                            </div>

                            <!-- Company Name 2 -->
                            <div class="row">
                                <!-- Company Name 2 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                2. {{ translate('Company Name') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_name_1') ? 'is-invalid' : '' }}" name="company_name_1" id="company_name_1" type="text" placeholder="{{ translate('Enter your Company Name') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_name_1') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Address 2 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Address') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_address_2') ? 'is-invalid' : '' }}" name="company_address_2" id="company_address_2" type="text" placeholder="{{ translate('Enter address for company 2') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_address_2') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Telephone No 2 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Telephone No') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_tel_2') ? 'is-invalid' : '' }}" name="company_tel_2" id="company_tel_2" type="text" placeholder="{{ translate('Telephone No for Company 2') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_tel_2') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Account # or name 2 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Account # or name') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('account_name_2') ? 'is-invalid' : '' }}" name="account_name_2" id="account_name_2" type="text" placeholder="{{ translate('exactly as it appears on acct') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('account_name_2') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 
                            </div>

                            <div class="row">
                                <!-- Company Name 3 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                3. {{ translate('Company Name') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_name_3') ? 'is-invalid' : '' }}" name="company_name_3" id="company_name_3" type="text" placeholder="{{ translate('Enter your Company Name') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_name_3') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Address 3 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Address') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_address_3') ? 'is-invalid' : '' }}" name="company_address_3" id="company_address_3" type="text" placeholder="{{ translate('Enter address for company 3') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_address_3') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Telephone No 3 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Telephone No') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('company_tel_3') ? 'is-invalid' : '' }}" name="company_tel_3" id="company_tel_3" type="text" placeholder="{{ translate('Telephone No for Company 3') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('company_tel_3') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <!-- Account # or name 3 -->
                                <div class="col-md-4">
                                    <div class="form-group ">
                                        <div class="input_fields">
                                            <label class="col-form-label">
                                                {{ translate('Account # or name') }}
                                                <span class="mandatory cls">*</span>
                                            </label>
                                            <input class="form-control {{ $errors->has('account_name_3') ? 'is-invalid' : '' }}" name="account_name_3" id="account_name_3" type="text" placeholder="{{ translate('exactly as it appears on acct') }}" >
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('account_name_3') {{ $message }} @enderror
                                                </div>
                                            </div> 
                                        </div>                                           
                                    </div>
                                </div> 

                                <div class="col-md-8">
                                    <div class="form_privacytext">
                                        <p>Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our <a href="#">privacy policy.</a></p>
                                    </div>
                                </div>
                            </div>

                            <div class="contact_form">
                                <div class="create_accountbottom orange_btn responisve_mobile">
                                    <button type="button" class="prev"  data-target=".address_tax_cls" >Previous</button>
                                    <button type="submit">Create Account</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>

            </div>
        </div>
    </div>
    
</form>