@extends('front.layout.master')
@section('content')

<style>
.user_img {width: 120px;height: 120px;/*background: var(--bs-primary);*/border-radius: 50%;display: flex;justify-content: center;align-items: center;position: relative;margin-bottom: 20px;z-index: 0;}
.user_img input#formValidationFile {position: absolute;width: 100%;height: 111px;opacity: 0;z-index: 99;}
.user_img img.camersblackimg {position: absolute;bottom: 0;right: 0; z-index: 99;}

</style>
<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('user.dashboard') }}">My Account</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="{{ route('user.profile') }}">Account Settings</a></li>
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
                    <div class="wisthlist_toprow">
                        <div class="top_addressrow">
                            <h2>Account Settings</h2>
                        </div>
                        <form id="{{ route('user.profile') }}" method="post" enctype="multipart/form-data">
                   
                            @csrf

                            <div class="row">

                                <div class="col-md-3"> 

                                    <div class="account_user" >
                                        <div class="user_img">
                                            <!-- <img src="{{ asset('assets/dist/images/aacountsetting_user.svg') }}"> -->
                                            <img class="camersblackimg" src="{{ asset('assets/dist/images/cameraimg.png') }}"> 
                                            <input class="form-control" onchange="loadFile(event,'image_1')" type="file" id="formValidationFile" name="profile_pic"> 
                                            <div class="mx-auto mb-3 mt-3" >
                                                <img
                                                src="{{ $get_user->image != '' ? get_image_upload('users/'.$get_user->image) : asset('placeholder/category/image.png') }}"
                                                id="image_1" alt="Avatar Image" class="rounded-circle w-px-100" style="width: 120px;height: 120px;object-fit: cover;position: relative;z-index: 9;" >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-9"></div>

                                <!-- First Name -->
                                <div class="col-md-6">
                                    <div class="input_fields">
                                        <label class="form-label" for="formValidationFirstName">First Name</label>
                                        <input type="text" id="formValidationFirstName" class="form-control {{ $errors->has('first_name') ? 'is-invalid':'' }}" placeholder="Enter First Name"
                                            name="first_name" value="{{ $get_user->first_name }}" />
                                        @error('first_name')
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationFirstName" data-validator="notEmpty">{{ $message }}</div>
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Last Name -->
                                <div class="col-md-6">
                                    <div class="input_fields">
                                        <label class="form-label" for="formValidationLastName">Last Name</label>
                                        <input type="text" id="formValidationLastName" class="form-control {{ $errors->has('last_name') ? 'is-invalid':'' }}" placeholder="Enter Last Name"
                                            name="last_name" value="{{ $get_user->last_name }}" />
                                        @error('last_name')
                                            <div class="fv-plugins-message-container fv-plugins-message-container--enabled invalid-feedback">
                                                <div data-field="formValidationLastName" data-validator="notEmpty">{{ $message }}</div>
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6">
                                    <div class="input_fields">
                                        <label class="form-label" for="formValidationEmail">Email</label>
                                        <input readonly class="form-control" type="email" id="formValidationEmail" placeholder="Email" value="{{ $get_user->email }}" />
                                    </div>
                                </div> 

                                <!-- Change Password -->
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="switch switch-primary">
                                            <input type="checkbox" name="is_change_password" {{ old("is_change_password") == 'on'?'checked':'' }} class="switch-input " id="changePasswordCheckbox" >
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                            <span class="switch-label">Change Password</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 password-field {{ old("is_change_password") == 'on'?'d-block':'d-none' }}">
                                    <div class="input_fields">
                                        <div class="form-password-toggle">
                                            <label class="form-label" for="formValidationPass">Old Password</label>
                                            <div class="input-group input-group-merge">
                                                <input class="form-control {{ $errors->has('old_password') ? 'is-invalid':'' }}"  type="password" id="formValidationPass" name="old_password" placeholder="" value="{{ old('old_password') }}" />
                                            </div>
                                           
                                            <div class="col-form-alert-label text-danger">
                                                @if ($errors->has('old_password'))
                                                    {{ $errors->first('old_password') }}
                                                @endif
                                            </div>   
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 password-field {{ old("is_change_password") == 'on'?'d-block':'d-none' }}">
                                    <div class="input_fields">
                                        <div class="form-password-toggle">
                                            <label class="form-label" for="formValidationPass">New Password</label>
                                            <div class="input-group input-group-merge">
                                                <input class="form-control {{ $errors->has('new_password') ? 'is-invalid':'' }}" type="password" id="formValidationPass"
                                                    name="new_password" placeholder=""
                                                    aria-describedby="multicol-password2" />
                                            </div>
                                           
                                            <div class="col-form-alert-label text-danger">
                                                @if ($errors->has('new_password'))
                                                    {{ $errors->first('new_password') }}
                                                @endif
                                            </div>  
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 password-field {{ old("is_change_password") == 'on'?'d-block':'d-none' }}">
                                    <div class="input_fields">
                                        <div class="form-password-toggle">
                                            <label class="form-label" for="formValidationConfirmPass">Confirm Password</label>
                                            <div class="input-group input-group-merge">
                                                <input class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid':'' }}" type="password" id="formValidationConfirmPass"
                                                    name="password_confirmation" placeholder=""
                                                    aria-describedby="multicol-confirm-password2" />
                                            </div>
                                            
                                            <div class="col-form-alert-label text-danger">
                                                @if ($errors->has('password_confirmation'))
                                                    {{ $errors->first('password_confirmation') }}
                                                @endif
                                            </div>  
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8 mt-3"> </div>
                                <div class="col-md-4 mt-3">
                                    <div class="create_accountbottom orange_btn" >
                                        <button type="submit" name="submitButton">Submit</button>
                                    </div>
                                </div>

                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@section('script')  
    <script>
        $(document).ready(function() {
            $('.password-field').addClass('d-block');
            $('#changePasswordCheckbox').change(function() {
                if ($(this).prop('checked')) {
                    $('.password-field').removeClass('d-none').addClass('d-block');
                } else {
                    $('.password-field').removeClass('d-block').addClass('d-none');
                }
            });
        });
    </script>
@endsection

@endsection