<!DOCTYPE html>



<html lang="en" class="light-style layout-wide  customizer-hide" dir="ltr" data-theme="theme-default"

    data-template="vertical-menu-template-free">



<head>

    <meta charset="utf-8" />

    <meta name="viewport"

        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />



    <title>Forget Password | Kiwi Business Products</title>

    <!-- Favicon -->

    <link rel="icon" type="image/x-icon"

        href="{{ asset('assets/images/favicon.png') }}" />



    <!-- Fonts -->

    <link rel="preconnect" href="https://fonts.googleapis.com/">

    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>

    <link

        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap"

        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />



    <!-- Core CSS -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}"

        class="template-customizer-core-css" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}"

        class="template-customizer-theme-css" />

    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />



    <!-- Vendors CSS -->

    <link rel="stylesheet"

        href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />



    <!-- Page CSS -->

    <!-- Page -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}">



    <!-- Helpers -->

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>

    <script src="{{ asset('assets/js/config.js') }}"></script>

    <style>
        .authentication-wrapper {flex-direction: column;}
        .title_subtitle {text-align: center;margin-bottom: 45px;}
        .title_subtitle h1 {font-size: 50px;line-height: 1.1;font-weight: 500;font-family: "Nunito Sans", sans-serif;color: #20273A;text-transform: uppercase;}
        .title_subtitle h1 span{ font-weight: 700;color: #FF6968;}
        .title_subtitle p {font-size: 20px;line-height: 1;font-weight: 500;font-family: "Nunito Sans", sans-serif;color: #20273A;}
    </style>

</head>



<body>



    <!-- Content -->

    <div class="container-xxl">

        <div class="authentication-wrapper authentication-basic container-p-y">

            <div class="title_subtitle">
                <h1>Welcome to <span>Kiwi Business Products</span> </h1>
                <p>Your one stop solution to all things LPG</p>
            </div>

            <div class="authentication-inner">



                <!-- Register Card -->

                <div class="card">

                    <div class="card-body">

                        <!-- Logo -->

                        <div class="app-brand justify-content-center">

                            <a href="#" class="app-brand-link gap-2">

                                <span class="app-brand-logo demo">



                                    <img  src="{{ asset('assets/images/logo.png') }}" />



                                </span>



                            </a>

                        </div>

                        <!-- /Logo -->



                        <form id="formAuthentication" class="mb-3" method="post"

                            action="{{ route('admin.forgot_password') }}">

                            @csrf



                            <div class="mb-3">

                                <label for="email" class="form-label">Email</label>

                                <input type="text" class="form-control" id="email" name="email"

                                    placeholder="Enter your email">

                                <div class="col-form-alert-label text-danger">

                                    @if ($errors->has('email'))

                                        {{ $errors->first('email') }}

                                    @endif

                                </div>

                            </div>

                            <button class="btn btn-primary d-grid w-100" type="submit"> Send Mail</button>

                            <div class="mb-3 mt-3">

                                <a href="{{ route('admin.login') }}">Back to Login</a>

                            </div>

                        </form>

                    </div>

                </div>

                <!-- Register Card -->

            </div>

        </div>

    </div>



    <div class="bs-toast toast toast-placement-ex m-2 bottom-0 start-0" role="alert" aria-live="assertive"

        aria-atomic="true" data-bs-delay="2000">

        <div class="toast-header">



            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>

        </div>

        <div class="toast-body">



        </div>

    </div>



    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <script src="{{ asset('assets/js/main.js') }}"></script>

    <script async defer src="{{ asset('assets/buttons.js') }}"></script>



    <script src="{{ asset('customjs/notify.js') }}"></script>

    <script src="{{ asset('customjs/notify.min.js') }}"></script>

    <script src="{{ asset('customjs/alert.js') }}"></script>

    <script src="{{ asset('customjs/duration.js') }}"></script>

    <script src="{{ asset('admin_assets/js/ui-toasts.js') }}"></script>



    @if ($errors->has('success'))

        <script>

            success_msg("{{ $errors->first('success') }}")

        </script>

    @endif

    @if ($errors->has('error'))

        <script>

            danger_msg("{{ $errors->first('error') }}");

        </script>

    @endif



</body>



</html>

