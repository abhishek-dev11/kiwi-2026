@extends('front.layout.master')
@section('content')

<div class="useraccount_section">
    <div class="container">
        <div class="useraccount_row">
            <div class="useraccountmain_box common_text">
                <img src="{{ asset('assets/dist/images/forgotpassword-img.png') }}">
                <h2 class="common_heading">Forgot password?</h2>
                <!-- <p>We’ve sent a password reset link to <br>user@email.com</p> -->
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif


                <form id="formAuthentication" class="mt-5" method="post" action="{{ route('front.forgot-password') }}">
                    @csrf
                    <div class="contact_form">
                        <div class="form_group">
                            <div class="input_fields" style="margin-bottom: 0px;">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="text" class="form-control" id="email" name="email" placeholder="Enter your Email Address" value="{{ old('email') }}"> 
                                <div class="col-form-alert-label text-danger">
                                    @if ($errors->has('email'))
                                        {{ $errors->first('email') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="submit_btn orange_btn">
                        <button type="submit"> Send Mail</button>
                    </div>
                </form> 

                <!-- <div class="mb-3 mt-3">
                    <a href="{{ route('user.login') }}">Back to Login</a>
                </div> -->

            </div>
        </div>
    </div>
</div>

@endsection