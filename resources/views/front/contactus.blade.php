@extends('front.layout.master')
@section('content')

<div class="contact_herosec">
    <div class="container">
        <div class="contact_herorow">
            <h2>{{ $data['hero_title'] }}</h2>
            <h1>{!! $data['hero_subtitle'] !!}</h1>
        </div>
    </div>
</div>

<div class="contact_secondsec">
    <div class="container">
        <div class="row">

            <div class="col-12 col-md-6 col-lg-6">
                <div class="contact_leftcol">

                    <div class="topcontact_us">
                        <h2 class="common_heading">{{ $data['contact_title'] }}</h2>
                        <p>{{ $data['contact_text'] }}</p>

                        <div class="customer_service">

                            <div class="service_info">
                                <p>Customer Service</p>
                                <div class="inner_col">
                                    <img src="{{ asset('assets/dist/images/phone_orangeicon.svg') }}">
                                    <a href="tel:{{ $data['contact_phone'] }}">{{ $data['contact_phone'] }}</a>
                                </div>
                            </div>

                            <div class="service_info">
                                <p>Chat with us here!</p>
                                <div class="inner_col">
                                    <img src="{{ asset('assets/dist/images/chat_orangeicon.svg') }}">
                                    <a href="#">{{ $data['chat_text'] }}</a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="middlecontact_us">
                        <h2 class="common_heading">{{ $data['info_title'] }}</h2>
                        <p>{{ $data['info_address'] }}</p>

                        <div class="customer_service">

                            <div class="service_info">
                                <p>Phone</p>
                                <div class="inner_col">
                                    <img src="{{ asset('assets/dist/images/phone_orangeicon.svg') }}">
                                    <a href="tel:{{ $data['info_phone'] }}">{{ $data['info_phone'] }}</a>
                                </div>
                            </div>

                            <div class="service_info">
                                <p>Email Address</p>
                                <div class="inner_col">
                                    <img src="{{ asset('assets/dist/images/chat_orangeicon.svg') }}">
                                    <a href="mailto:{{ $data['info_email'] }}">
                                        {{ $data['info_email'] }}
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="bottomcontact_us">
                        <img src="{{ $data['contact_image'] }}">
                    </div>

                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-6">
                <div class="contact_rightcol">

                    <div class="right_toprow common_text relative_cls">

                        <div id="common_loader">
                            <div>
                                <img src="{{ asset('assets/images/loader.gif') }}">
                            </div>
                        </div>

                        <h2 class="common_heading">{{ $data['form_title'] }}</h2>
                        <p>{{ $data['form_text'] }}</p>

                        <form method="POST" action="{{ route('contactus') }}" id="ContactFormID">
                            @csrf
                            <div class="contact_form">

                                <div class="form_group">
                                    <div class="input_fields" id="Input-name">
                                        <label>Your Name</label>
                                        <input type="text" name="name" placeholder="Enter your name">
                                        <span class="help-block"></span>
                                    </div>

                                    <div class="input_fields" id="Input-phone">
                                        <label>Phone Number</label>
                                        <input type="text" name="phone" placeholder="Enter your phone number">
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="form_group">
                                    <div class="input_fields" id="Input-email">
                                        <label>Email Address</label>
                                        <input type="text" name="email" placeholder="Enter your email address">
                                        <span class="help-block"></span>
                                    </div>

                                    <div class="input_fields" id="Input-capital_answer">
                                        <label>The capital of NewZealand?</label>
                                        <input type="text" name="capital_answer" placeholder="Enter your answer">
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="textarea_field">
                                    <div class="input_fields" id="Input-message">
                                        <label>Your Message</label>
                                        <textarea name="message" placeholder="Enter your message"></textarea>
                                        <span class="help-block"></span>
                                    </div>
                                </div>

                                <div class="submit_btn orange_btn">
                                    <button type="submit">Submit</button>
                                </div>

                            </div>
                        </form>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<div class="map_section">
    <iframe
        src="{{ $data['map_embed_url'] }}"
        width="100%"
        height="450"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).on("submit", "#ContactFormID", function(e) {
        e.preventDefault();

        $('#ContactFormID .input_fields').removeClass('has-error');
        $('#ContactFormID .help-block').html('');

        var formData = new FormData(this);
        $.ajax({
            type: "POST",
            url: "{{ route('contactus') }}",
            datatype: 'JSON',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#ContactFormID #common_loader').show();
            },
            success: function(response) {
                $('#ContactFormID #common_loader').hide();
                if (response.error) {
                    danger_msg(response.message);
                    var i = 1;
                    jQuery.each(response.data, function(index, value) {
                        if (value != '') {
                            $('#Input-' + index).addClass('has-error');
                            $('#Input-' + index + ' .help-block').html(value);
                            if (i == 1) {
                                $('#Input-' + index).find('input, textarea').focus();
                            }
                            i++;
                        }
                    });
                } else {
                    success_msg(response.message);
                    $('#ContactFormID')[0].reset();
                }
            },
            error: function() {
                $('#ContactFormID #common_loader').hide();
                danger_msg('Something went wrong!');
            }
        });
        return false;
    });
</script>
@endsection
