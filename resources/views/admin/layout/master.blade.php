<!DOCTYPE html>

<html lang="en" class="light-style layout-menu-fixed layout-compact " dir="ltr" data-theme="theme-default" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ isset($common['title']) ? $common['title'] : '' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/favicon.png') }}" /> 
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">

 
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <!-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" class="template-customizer-core-css" /> -->
    <!-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" class="template-customizer-core-css" /> -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- <link rel="stylesheet" href="{{ asset('assets/css/items.css') }}" /> -->
    <link rel="stylesheet" href="{{ asset('customjs/common.css') }}" />
    <link rel="stylesheet" href="{{ asset('customjs/css/select2.min.css') }}" />
 
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    
    <!-- Page CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- DatePicker -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

    <style type="text/css">
        

        /* Custom loader animation */
        .swal2-loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
        }

        button.swal2-confirm.swal2-styled {background-color: #465785;width: 88px;}

        /* Animation for the loader */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
        .cke_notifications_area {display: none;}
    </style>
</head>

<body>

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar  ">
        <div class="layout-container">
            {{-- SiderBar --}}

            @include('admin.layout.sidebar')
            {{-- SiderBar --}}

            <!-- Layout container -->
            <div class="layout-page">
                {{-- SiderBar --}}
                @include('admin.layout.header')
                {{-- SiderBar --}}

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    @yield('content')
                    <!-- / Content -->

                    {{-- <!-- Footer --> --}}
                    @include('admin.layout.footer')
                    {{-- <!-- / Footer --> --}}

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->

        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>

    </div>
    <!-- / Layout wrapper -->

    <div class="bs-toast toast toast-placement-ex m-2 bottom-0 start-0" role="alert" aria-live="assertive"
        aria-atomic="true" data-bs-delay="2000">
        <div class="toast-header">
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">

        </div>
    </div>
 
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('customjs/js/select2.min.js') }}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="{{ asset('assets/buttons.js') }}"></script>
    {{-- <script src="{{ asset('customjs/notify.js') }}"></script>
    <script src="{{ asset('customjs/notify.min.js') }}"></script> --}}

    <script src="{{ asset('customjs/alert.js') }}"></script>
    <script src="{{ asset('customjs/duration.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('customjs/js/form-extra.js') }}"></script>
    <script src="{{ asset('customjs/js/autosize.js') }}"></script>
    <script src="{{ asset('customjs/js/jquery-repeater.js') }}"></script>
    <script src="{{ asset('assets/js/ui-toasts.js') }}"></script>
     
    

    <script>
        // Function to show the SweetAlert with a loader
        function showLoader() {
            Swal.fire({
                title: 'Processing...',
                html: 'Please wait while we fetch the data.',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    const loader = document.createElement('div');
                    loader.classList.add('swal2-loader');
                    document.querySelector('.swal2-html-container').appendChild(loader);
                }
            });
        }
    </script>

    <script src="https://cdn.tiny.cloud/1/bsz1tznoh1hnv3upbfi1z2pwmbstchf67ucjk4r3v3jp4nu3/tinymce/5-stable/tinymce.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.16.2/full/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.ckeditor').forEach(function (textarea) {
                CKEDITOR.replace(
                    textarea,
                    {
                        extraPlugins: 'colorbutton,colordialog',
                        toolbar: [
                            { name: 'colors', items: [ 'TextColor', 'BGColor', 'colordialog' ] },
                            // Add other toolbar groups if needed
                        ],
                    }
                );
            });
        });
    </script>

    <script>
        tinymce.init({
            selector: '.tinymce',
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: "aligncenter alignjustify alignleft alignnone alignright| anchor | blockquote blocks | backcolor | bold | copy | cut | fontfamily fontsize forecolor h1 h2 h3 h4 h5 h6 hr indent | italic | language | lineheight | newdocument | outdent | paste pastetext | print | redo | remove removeformat | selectall | strikethrough | styles | subscript superscript underline | undo | visualaid | a11ycheck advtablerownumbering typopgraphy anchor restoredraft casechange charmap checklist code codesample addcomment showcomments ltr rtl editimage fliph flipv imageoptions rotateleft rotateright emoticons export footnotes footnotesupdate formatpainter fullscreen help image insertdatetime link openlink unlink bullist numlist media mergetags mergetags_list nonbreaking pagebreak pageembed permanentpen preview quickimage quicklink quicktable cancel save searchreplace spellcheckdialog spellchecker | table tablecellprops tablecopyrow tablecutrow tabledelete tabledeletecol tabledeleterow tableinsertdialog tableinsertcolafter tableinsertcolbefore tableinsertrowafter tableinsertrowbefore tablemergecells tablepasterowafter tablepasterowbefore tableprops tablerowprops tablesplitcells tableclass tablecellclass tablecellvalign tablecellborderwidth tablecellborderstyle tablecaption tablecellbackgroundcolor tablecellbordercolor tablerowheader tablecolheader | tableofcontents tableofcontentsupdate | template typography | insertfile | visualblocks visualchars | wordcount",
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });
    </script>

    <script>
        $(".single-select").select2();

        $('.single-modal-select').select2({
            dropdownParent: $('#setupShop')
        });

        $(".single-select_order").on("select2:select", function (evt) {
            let element = evt.params.data.element;
            let $element = $(element);

            $element.detach();
            $(this).append($element);
            $(this).trigger("change");
        });
    </script>

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

    <script>
        
        $(document).on("click", ".confirm-delete", function() {
            $('#ItemModal').modal('hide');
            var link = $(this).data("href");
            Swal.fire({
                title: 'Are you sure you want to Delete ?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                confirmButtonColor: "#8486e3",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = link;
                }
            })
        });

        function deleteMsg(message) {
            return Swal.fire({
                title: message,
                showCancelButton: true,
                returnInputValueOnDeny: true,
                confirmButtonText: `Delete`,
                denyButtonText: `Cancel`,
            })

        }

        function confirmMsg(title, message) {
            return Swal.fire({
                title: title,
                text: message,
                showCancelButton: true,
                confirmButtonColor: 'rgb(155 176 199)',
                cancelButtonColor: 'rgb(227 46 46)',
                confirmButtonText: 'Yes, delete it!'
            })
        }

        function warningMsg(title, message) {
            return Swal.fire({
                icon: 'error',
                title: title,
                text: message,
            })
        }
    </script>
 
    @yield('script')
</body>

</html>
