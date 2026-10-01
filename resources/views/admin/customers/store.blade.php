@extends('admin.layout.master')
@section('content')

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card shadow mb-4">
            <div class="card-header">
                <strong class="card-title f-18">{{ $common['title'] }}</strong>

                <a href="{{ route('admin.customers') }}" class="btn btn-secondary float-end">
                    <span class="tf-icons bx bx-chevrons-left me-1"></span>{{ translate('Back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="POST" class="relative_cls" action="{{ route('admin.customer.add') }}" enctype="multipart/form-data" id="addFrom">
                    @csrf
                    <input type="hidden" name="id" value="{{ $get_customer['id'] }}">
                    
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
                                    <input type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" id="password"
                                        type="text" {{$get_customer['password'] != "" ? 'disabled': '' }} value="{{ old('phone', $get_customer['password']) }}"
                                        placeholder="{{ translate('Enter Password') }}">
                                    <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                        <div data-field="formValidationregular_price" data-validator="notEmpty" class="error_msg">@error('password') {{ $message }} @enderror</div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
            url: "{{ route('admin.customer.add') }}",
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
                        window.location.href = "{{ route('admin.customers') }}";
                    }, 1000);
                }
            }
        });
        return false;
    });
</script>

@endsection