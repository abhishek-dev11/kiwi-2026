@extends('front.layout.master')
@section('content')
<div class="useraccount_section login_section loginmainpg">
    <div class="container">
        <div class="useraccount_row">
            <div class="useraccountmain_box common_text relative_cls">
                <div id="common_loader">
                    <div>
                        <img src="{{ asset('assets/images/loader.gif') }}">
                    </div>
                </div>
                <img src="{{ asset('assets/dist/images/accountpage_logo.png') }}">
                <h2 class="common_heading">Login</h2>
                <p>We missed you! Please enter your details.</p>
                <form  method="POST" action="{{ route('user.login') }}" id="LoginFromID" class="">


                    @csrf
                    <div class="contact_form"> 

                        <!-- Email -->
                        <div class="col-md-12">
                            <div class="input_fields" id="Input-email" >
                                <label class="col-form-label">{{ translate('Email') }} <span class="mandatory cls">*</span></label>
                                <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" id="email"
                                    type="text" value=""
                                    placeholder="{{ translate('Enter your Email ID') }}" autocomplete="off">
                                <span class="help-block"></span>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-md-12">
                            <div class=" input_fields" id="Input-password" >
                                <label class="col-form-label">{{ translate('Password') }} <span class="mandatory cls">*</span></label>
                                <input type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" id="password"
                                    type="text" value=""
                                    placeholder="{{ translate('Enter your password') }}">
                                <span class="help-block"></span>
                                <div class="reset_password">
                                    <a href="{{ route('front.forgot-password') }}">Forgot password?</a>
                                </div>
                            </div>
                        </div>
 
                        <div class="submit_btn orange_btn">
                            <button type="submit">Login</button>
                        </div>

                        <div class="setnew_account">
                            <p>
                                Don’t have an account?
                                <a href="{{ route('front.signup') }}">Create new account</a>
                            </p>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection