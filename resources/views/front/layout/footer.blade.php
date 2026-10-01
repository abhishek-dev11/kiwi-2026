<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="useraccount_section login_section">
                    <div class="useraccount_row">
                        <div class="useraccountmain_box common_text">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            <img src="{{ asset('assets/dist/images/accountpage_logo.png') }}">
                            <h2 class="common_heading">Login</h2>
                            <p>We missed you! Please enter your details.</p>
                            <form  method="POST" action="{{ route('user.login') }}" id="LoginFromID">
                                @csrf

                                <div class="relative_cls" >
                                    <div id="common_loader">
                                        <div>
                                            <img src="{{ asset('assets/images/loader.gif') }}">
                                        </div>
                                    </div>
                                </div>

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
        </div>
    </div>
</div>

<footer class="footer_section">
    <div class="container">

        <div class="footer_top">
            <div class="footer_col first_footercol">
                <a href="{{ route('home') }}">
                    <img src="{{ $siteSettings['footer_top_logo_url'] }}">
                </a>
                <p>{{ $siteSettings['footer_top_text'] ?: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry...' }}</p>
            </div>

            <div class="footer_col second_footercol">
                <h4>Links</h4>
                <ul>
                    <li class="active"><a href="">Home</a></li>
                    <li><a href="">Shop</a></li>
                    <li><a href="{{ route('aboutus') }}">About Us</a></li>
                    <li><a href="{{ route('contactus') }}">Contact Us</a></li>
                    <li><a href="">Login</a></li>
                    <li><a href="">Register</a></li>
                    <li><a href="">Trade account</a></li>
                </ul>
            </div>

            <div class="footer_col">
                <h4>Policy</h4>
                <ul>
                    <li><a href="{{ route('privacy-policy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('shipping-policy') }}">Shipping Policy</a></li>
                    <li><a href="{{ route('terms-conditions') }}">Terms & Conditions</a></li>
                    <li><a href="{{ route('returns-policy') }}">Returns Policy</a></li>
                    <li><a href="{{ route('disclaimer') }}">Disclaimer</a></li>
                </ul>
            </div>

            <div class="footer_col footcontact_info">
                <h4>Contact info</h4>
                @php
                    $phone = $siteSettings['footer_phone_4'] ?: '0272132533';
                    $email = $siteSettings['footer_email_4'] ?: 'steve@kiwibusinessproducts.co.nz';
                @endphp
                <div class="footercontact_info">
                    <div class="inner_row">

                        <div class="phone_info">
                            <p>{{ $siteSettings['footer_phone_4_text'] ?: 'Phone no.' }}</p>
                            <div class="info_inner">
                                <img src="{{ asset('assets/dist/images/phone-icon.png') }}">
                                <a href="tel:{{ $phone }}">{{ $phone }}</a>
                            </div>
                        </div>

                        <div class="email_info">
                            <p>{{ $siteSettings['footer_email_4_text'] ?: 'Email Address' }}</p>
                            <div class="info_inner">
                                <img src="{{ asset('assets/dist/images/email-icon.png') }}">
                                <a href="mailto:{{ $email }}">{{ $email }}</a>
                            </div>
                        </div>

                    </div>

                    <div class="footsocial_iicons">
                        <ul class="social_icons">
                            <li><a href="{{ $siteSettings['facebook_link'] ?: '#' }}" target="_blank" rel="noopener"><img src="{{ asset('assets/dist/images/facebook-icon.svg') }}"></a></li>
                            <li><a href="{{ $siteSettings['instagram_link'] ?: '#' }}" target="_blank" rel="noopener"><img src="{{ asset('assets/dist/images/instagram-icon.svg') }}"></a></li>
                            <li><a href="{{ $siteSettings['linkedin_link'] ?: '#' }}" target="_blank" rel="noopener"><img src="{{ asset('assets/dist/images/linkdin-icon.svg') }}"></a></li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <div class="footer_bottom">
            <p>{{ $siteSettings['footer_copyright_text'] ?: 'Copyright © ' . date('Y') . ' Kiwi Business Products, All rights reserved.' }}</p>
        </div>
    </div>
</footer>
