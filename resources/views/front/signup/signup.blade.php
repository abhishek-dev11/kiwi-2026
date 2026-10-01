@extends('front.layout.master')
@section('content')
    <style>
        .invalid-feedback {display: block !important;}
    </style>
    <div class="useraccount_section create_account create_accountbysteps">
        <div class="container">
            <div class="create_accountrow">
                <div class="useraccountmain_box common_text" id="WidthAreaID">
                    <img src="{{ asset('assets/dist/images/accountpage_logo.png') }}">
                    <h2 class="common_heading">Cerate Account</h2>
 
                    <div class="tab_btnrow">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs tabs"  id="SignupTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="tab-btn active"
                                      id="guest-tab"
                                      data-bs-toggle="tab"
                                      data-bs-target="#guest"
                                      type="button"
                                      role="tab">
                                    Guest User
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="tab-btn"
                                      id="tradeid-tab"
                                      data-bs-toggle="tab"
                                      data-bs-target="#tradeid"
                                      type="button"
                                      role="tab">
                                    Trade Account User
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">

                         
                        <!-- GUEST -->
                        <div class="tab-pane fade show active" id="guest" role="tabpanel">
                            @include('front.signup.customer')
                        </div>
                        <div class="tab-pane fade" id="tradeid" role="tabpanel">
                            @include('front.signup.trade')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@section('script')
<!-- Customer Jquery -->
<script type="text/javascript">
    $(document).on("submit", "#CustomerFromID", function(e) {

        $('#CustomerFromID .input_fields').removeClass('has-error');
        $('#CustomerFromID .help-block').html('');

        e.preventDefault();
        var formData = new FormData(this);
        $('.error_msg').text("");
        $.ajax({
            type: "POST",
            url: "{{ route('front.customer_signup') }}",
            datatype: 'JSON',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function(result){
               //jQuery('#common_loader').show();
            },
            success: function(response) {
                //jQuery('#common_loader').hide();
                if (response.error) {
                    danger_msg("Something is wrong with required field!");
                    var i = 1;
                    jQuery.each( response.error, function( index, value ) {
                        if(value!=''){
                            $('#Input-'+index).addClass('has-error');
                            $('#Input-'+index+' .help-block').html(value);
                            if(i==1){   
                                $('#'+index).focus();
                            }
                            i++;
                        }
                    });
                } else {
                    success_msg(response.success);
                    setTimeout(() => {
                        window.location.href = "{{ route('user.login') }}";
                    }, 5000);
                }
            }
        });
        return false;
    });
</script>

<!-- Trade Customer Jquery -->
<script type="text/javascript">
    $(document).on("submit", "#TradeFromID", function(e) {
        e.preventDefault();
        /*success_msg('sadd');
        console.log('sadsdasd');
        return false;*/

        var formData = new FormData(this);
        $('.error_msg').text("");
        $.ajax({
            type: "POST",
            url: "{{ route('front.trade_signup') }}",
            datatype: 'JSON',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function(result){
               //jQuery('#common_loader').show();
            },
            success: function(response) {
                //jQuery('#common_loader').hide();
                if (response.error) {
                    $("#TradeFromID .tab-pane").removeClass('active show');
                    $('#TradeFromID .nav-link').removeClass('active');
                    danger_msg("Something is wrong with required field!");
                    activeTab(response.error);
                    updateStepClasses();
                } else {
                    success_msg(response.success);
                    setTimeout(() => {
                        //window.location.href = "{{ route('user.login') }}";
                    }, 1000);
                }
            }
        });
        return false;
    });

    $(document).on('click', '.prev', function () {
        const target = $(this).data('target');
        $(target).trigger('click');
        $('html, body').animate({
            scrollTop: $('form#TradeFromID').offset().top - 90
        }, 1000);
    });

    function updateStepClasses() {
        const $tabs = $('.nav-tabs .nav-link');
        const activeIndex = $tabs.index($('.nav-tabs .nav-link.active'));
        $tabs.removeClass('completed');
        $tabs.each(function(index) {
            if (index <= activeIndex) {
                $(this).addClass('completed');
            }
        });
    }
    
    // Run on page load
    updateStepClasses();

    // Run when tab changes
    $(document).on('click', '.nav-tabs .nav-link', function () {
        setTimeout(function () {
            updateStepClasses();
        }, 50);
    });
    
</script>

 
<script type="text/javascript">
    
    $('body').on('click', '#SignupTab .tab-btn', function(e){
        var  get_id = $(this).attr('id');
        if( get_id == 'tradeid-tab' ){
            $('#WidthAreaID').addClass('trade-active');
        }else{
            $('#WidthAreaID').removeClass('trade-active');
        }
    });

    $('body').on('click', '#TradeFromID .next', function(e){
        $("#TradeFromID").trigger("submit");
    });
</script>

@endsection