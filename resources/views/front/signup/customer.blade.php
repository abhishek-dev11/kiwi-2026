<form method="POST" class="relative_cls" action="{{ route('front.customer_signup') }}" enctype="multipart/form-data" id="CustomerFromID">
    @csrf

    <div class="row">

        <!-- First Name -->
        <div class="col-md-12">
            <div class="input_fields"  id="Input-first_name" >
                <label class="col-form-label">{{ translate('First Name') }} <span class="mandatory cls">*</span></label>
                <input class="form-control {{ $errors->has('first_name') ? 'is-invalid' : '' }}" name="first_name" id="first_name"
                    type="text" value=""
                    placeholder="{{ translate('Enter First Name') }}" >
                <span class="help-block"></span>
            </div>                                        
        </div>     

        <!-- Last Name -->
        <div class="col-md-12">
             <div class="input_fields" id="Input-last_name" >
                <label class="col-form-label">{{ translate('Last Name') }} <span class="mandatory cls">*</span></label>
                <input class="form-control {{ $errors->has('last_name') ? 'is-invalid' : '' }}" name="last_name" id="last_name"
                    type="text" value=""
                    placeholder="{{ translate('Enter Last Name') }}" >
                <span class="help-block"></span>                                           
            </div>                                            
         </div>  

        <!-- Email -->
        <div class="col-md-12">
            <div class="input_fields" id="Input-email" >
                <label class="col-form-label">{{ translate('Email') }} <span class="mandatory cls">*</span></label>
                <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" id="email"
                    type="text" value=""
                    placeholder="{{ translate('Enter Email') }}" autocomplete="off">
                <span class="help-block"></span>
            </div>
        </div>


        <!-- Password -->
        <div class="col-md-12">
            <div class=" input_fields" id="Input-password" >
                <label class="col-form-label">{{ translate('Password') }} <span class="mandatory cls">*</span></label>
                <input type="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" id="password"
                    type="text" value=""
                    placeholder="{{ translate('Enter Password') }}">
                <span class="help-block"></span>
            </div>
        </div>

        <!-- Confirm Password -->
        <div class="col-md-12">
            <div class="input_fields" id="Input-confirm_password" >
                <label class="col-form-label">{{ translate('Confirm Password') }} <span class="mandatory cls">*</span></label>
                <input type="password" class="form-control {{ $errors->has('confirm_password') ? 'is-invalid' : '' }}" name="confirm_password" id="confirm_password"
                    type="text" value=""
                    placeholder="{{ translate('Enter Confirm Password') }}">
                <span class="help-block"></span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="submit_btn orange_btn">
                <button type="submit" class="btn btn-primary">
                    Register
                </button>
            </div>
        </div>
    </div>
</form>
<div class="setnew_account">
    <p>Already have an account?<a href="{{ route('user.login') }}"> Login</a></p>
</div>