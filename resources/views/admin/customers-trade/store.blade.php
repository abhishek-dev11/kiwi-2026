@extends('admin.layout.master')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.tradecustomers') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" class="relative_cls" action="{{ route('admin.tradecustomer.add') }}" enctype="multipart/form-data" id="addFrom">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_customer['id'] }}">
                    <div class="row">
                        <div class="col">
                            <div class="nav-align-top mb-3">

                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                      <button type="button" class="nav-link <?php if($errors->has('tab')){ if($errors->first('tab')=='GeneralInformation'){ ?> active <?php } }else{ ?> active <?php } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-GeneralInformation" role="tab" aria-selected="true">Basic & Personal Information</button>
                                    </li>
                                    <li class="nav-item">
                                      <button type="button" class="nav-link <?php if($errors->has('tab')){ if($errors->first('tab')=='companydetails'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-companydetails" role="tab" aria-selected="false">Company Basic Info</button>
                                    </li>
                                    <li class="nav-item">
                                      <button type="button" class="nav-link <?php if($errors->has('tab')){ if($errors->first('tab')=='address_tax'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-address_tax" role="tab" aria-selected="false">Address & Tax Details</button>
                                    </li>

                                    <li class="nav-item">
                                      <button type="button" class="nav-link <?php if($errors->has('tab')){ if($errors->first('tab')=='delivery_info'){ ?> active <?php } } ?>" data-bs-toggle="tab" data-bs-target="#form-tabs-delivery_info" role="tab" aria-selected="false">Delivery & Trade Information</button>
                                    </li>

                                </ul>

                                <div class="tab-content">

                                    <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='GeneralInformation'){ ?> active show <?php } }else{ ?> active show <?php } ?>" id="form-tabs-GeneralInformation" role="tabpanel">
                                        <div class="row">

                                            <!-- First Name -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 ">
                                                    <label class="col-form-label">{{ translate('First Name') }} <span class="mandatory cls">*</span></label>
                                                    <input class="form-control {{ $errors->has('first_name') ? 'is-invalid' : '' }}" name="first_name" id="first_name"
                                                        type="text" value="{{ old('first_name', $get_customer['first_name']) }}"
                                                        placeholder="{{ translate('Enter First Name') }}" >
                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                        <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('first_name') {{ $message }} @enderror
                                                        </div>
                                                    </div>                                            
                                                </div>
                                            </div>     

                                            <!-- Last Name -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 ">
                                                    <label class="col-form-label">{{ translate('Last Name') }} <span class="mandatory cls">*</span></label>
                                                    <input class="form-control {{ $errors->has('last_name') ? 'is-invalid' : '' }}" name="last_name" id="last_name"
                                                        type="text" value="{{ old('last_name', $get_customer['last_name']) }}"
                                                        placeholder="{{ translate('Enter Last Name') }}" >
                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                        <div data-field="formValidationName" data-validator="notEmpty" class="error_msg" >@error('last_name') {{ $message }} @enderror
                                                        </div>
                                                    </div>                                            
                                                </div>
                                            </div>  

                                            <!-- Email -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 ">
                                                    <label class="col-form-label">{{ translate('Email') }} <span class="mandatory cls">*</span></label>
                                                    <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" id="email"
                                                        type="text" {{$get_customer['email'] != "" ? 'disabled': '' }} value="{{ old('email', $get_customer['email']) }}"
                                                        placeholder="{{ translate('Enter Email') }}" autocomplete="off">
                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                        <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('email') {{ $message }} @enderror</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Phone Number -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 ">
                                                    <label class="col-form-label">{{ translate('Phone Number') }} <span class="mandatory cls">*</span></label>
                                                    <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" name="phone" id="phone"
                                                        type="text" value="{{ old('phone', $get_customer['phone']) }}"
                                                        placeholder="{{ translate('Enter Phone Number') }}">
                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                        <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('phone') {{ $message }} @enderror</div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if( $get_customer['id'] == '' )
                                                <!-- Password -->
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3 ">
                                                        <label class="col-form-label">{{ translate('Password') }} <span class="mandatory cls">*</span></label>
                                                        <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" id="password"
                                                            type="text" {{$get_customer['password'] != "" ? 'disabled': '' }} value="{{ old('phone', $get_customer['password']) }}"
                                                            placeholder="{{ translate('Enter Password') }}">
                                                        <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                            <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('password') {{ $message }} @enderror</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Customer Groups -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 ">
                                                    <label class="col-form-label">{{ translate('Customer Groups') }} 
                                                        <span class="mandatory cls">*</span>
                                                    </label>
                                                    <select class="form-control select2 {{ $errors->has('customer_group_id') ? 'is-invalid' : '' }}" name="customer_group_id" id="customer_group_id">
                                                        <option value="">Select Customer Group</option>
                                                        <?php foreach ($get_customer_groups as $key => $value) { ?>
                                                            <option value="<?php echo $value->id; ?>" {{ getSelected($value->id, old('customer_group_id', $get_customer['customer_group_id'])) }} >
                                                                <?php echo $value->group_name; ?>
                                                                ( {{ $value->discount_percentage }} % )
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                        <div data-field="formValidationcity_id" data-validator="notEmpty" class="error_msg">@error('customer_group_id') {{ $message }} @enderror </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status -->
                                            <div class="col-md-6">
                                                <div class="form-group mb-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                                    <label class="col-form-label" for="status">{{ translate('Status') }} <span class="mandatory cls">*</span></label>
                                                    <select name="status" id="status" class="form-select single-select"
                                                        data-allow-clear="true">
                                                        <option value="Active" {{ getSelected('Active', $get_customer['status']) }}>
                                                            {{ translate('Active') }}</option>
                                                        <option value="Deactive" {{ getSelected('Deactive', $get_customer['status']) }}>
                                                            {{ translate('Deactive') }}</option>
                                                    </select>
                                                </div>
                                            </div>                                       
                                        </div>
                                    </div>

                                    <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='companydetails'){ ?> active show <?php } } ?>" id="form-tabs-companydetails" role="tabpanel">
                                         
                                    </div>

                                    <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='address_tax'){ ?> active show <?php } } ?>" id="form-tabs-address_tax" role="tabpanel">

                                        <div class="row">
                                            <!-- Country -->
                                            <div class="col-md-4">
                                                <div class="form-group mb-3 ">
                                                    <label class="col-form-label">{{ translate('Country') }} 
                                                        <span class="mandatory cls">*</span>
                                                    </label>
                                                    <select class="form-control select2 {{ $errors->has('country_id') ? 'is-invalid' : '' }}" name="country_id" id="country_id">
                                                        <option value="">Select Country</option>
                                                        <?php foreach ($get_countries as $key => $value) { ?>
                                                            <option value="<?php echo $value->id; ?>" {{ getSelected($value->id, old('country_id', $get_customer['country'])) }} ><?php echo $value->name; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                        <div data-field="formValidationcity_id" data-validator="notEmpty" class="error_msg">@error('city_id') {{ $message }} @enderror </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- State -->
                                            <div class="col-md-4">
                                                <div class="form-group mb-3 ">
                                                    <label class="col-form-label">{{ translate('State') }} <span class="mandatory cls">*</span></label>
                                                    <select class="form-control select2 {{ $errors->has('state_id') ? 'is-invalid' : '' }}" name="state_id" id="state_id">
                                                        <option value="">Select State</option>
                                                    </select>
                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                        <div data-field="formValidationcity_id" data-validator="notEmpty" class="error_msg">@error('city_id') {{ $message }} @enderror </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- City -->
                                            <div class="col-md-4">
                                                <div class="form-group mb-3 ">
                                                    <label class="col-form-label">{{ translate('City') }}</label>
                                                    <select class="form-control select2 {{ $errors->has('city_id') ? 'is-invalid' : '' }}" name="city_id" id="city_id">
                                                        <option value="">Select City</option>
                                                    </select>
                                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                        <div data-field="formValidationcity_id" data-validator="notEmpty" class="error_msg">@error('city_id') {{ $message }} @enderror </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="tab-pane fade <?php if($errors->has('tab')){ if($errors->first('tab')=='delivery_info'){ ?> active show <?php } } ?>" id="form-tabs-delivery_info" role="tabpanel">
                                        
                                    </div>
                                     
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
    </div>
@endsection


@section('script')
<script type="text/javascript">
    $(document).on("submit", "#addFrom", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $('.error_msg').text("");
        $.ajax({
            type: "POST",
            url: "{{ route('admin.tradecustomer.add') }}",
            datatype: 'JSON',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function(result){
               jQuery('#common_loader').show();
            },
            success: function(response) {
                jQuery('#common_loader').hide();
                if (response.error) {
                    $(".tab-pane").removeClass('active show');
                    $('.nav-link').removeClass('active');
                    danger_msg("Something is wrong with required field!");
                    activeTab(response.error);
                } else {
                    success_msg(response.success);
                    setTimeout(() => {
                        window.location.href = "{{ route('admin.tradecustomers') }}";
                    }, 1000);
                }
            }
        });
        return false;
    });
</script>

<script type="text/javascript">
    //country_id
    $(document).ready(function () { 
        $(document).on("change", '#country_id', function() {
            select_states($(this).val());
        });
    });
    <?php if(old('country_id', null) != null){ ?>
        <?php if(old('state_id', null) != null){ ?>
            select_states('<?php echo old('country_id', null); ?>','<?php echo old('state_id', null); ?>');
        <?php }else{ ?>
            select_states('<?php echo old('country_id', null); ?>');
        <?php } ?>
    <?php }elseif($get_customer['country']){ ?>
        <?php if($get_customer['state']){ ?>
            select_states('<?php echo $get_customer['country']; ?>','<?php echo $get_customer['state']; ?>');
        <?php }else{ ?>
            select_states('<?php echo $get_customer['country']; ?>');
        <?php } ?>
    <?php } ?>



    //state_id
    $(document).ready(function () { 
        $(document).on("change", '#state_id', function() {
            select_cities($(this).val());
        });
    });

    <?php if(old('state_id', null) != null){ ?>
        <?php if(old('city_id', null) != null){ ?>
            select_cities('<?php echo old('state_id', null); ?>','<?php echo old('city_id', null); ?>');
        <?php }else{ ?>
            select_cities('<?php echo old('state_id', null); ?>');
        <?php } ?>
    <?php }elseif($get_customer['state']){ ?>
        <?php if($get_customer['city']){ ?>
            select_cities('<?php echo $get_customer['state']; ?>','<?php echo $get_customer['city']; ?>');
        <?php }else{ ?>
            select_cities('<?php echo $get_customer['state']; ?>');
        <?php } ?>
    <?php } ?>


    function select_states(country_id,child='0'){
        var label_title = "City";
        label_title = "State";
        $.ajax({
            url: "{{ route('admin.get_state_by_country_id') }}",
            type: 'post',
            data: {
                'title': label_title,
                'country_id': country_id,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#state_id').html(response);
            }
        });
    }

     function select_cities(state_id,child='0'){
        var label_title = "City";
        var country_id = $('#country_id').val();
        $.ajax({
            url: "{{ route('admin.get_city_by_state_id') }}",
            type: 'post',
            data: {
                'title': label_title,
                'country_id': country_id,
                'state_id': state_id,
                'child': child,
                "_token": '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#city_id').html(response);
            }
        });
    }
</script>

@endsection