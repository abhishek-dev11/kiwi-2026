<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ $siteSettings['favicon_url'] }}" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/dist/css/bootstrap.min.css') }}">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" /> -->
    <!-- DatePicker -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="{{ asset('assets/dist/css/slick.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dist/css/slick-theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('customjs/css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/dist/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dist/css/responsive.css') }}">

    {{-- Product image fallback: any product image that fails to load (dead / invalid / blocked
         supplier URL, missing local file) is swapped for the default placeholder. Product
         images are the ones served from the product upload folders or from another origin
         (supplier URLs). Runs for every <img> on the page, including ones added later by
         JavaScript (sliders, ajax lists), without touching layout or the individual templates. --}}
    <script>
        (function () {
            var placeholder = @json(asset('placeholder/product/image.png'));
            var localFolders = /\/uploads\/(Product|ProductGallery|ProductVariant)\//i;

            function isProductImage(src) {
                if (!src || src === placeholder) return false;
                if (localFolders.test(src)) return true;
                try {
                    var u = new URL(src, location.href);
                    return /^https?:$/.test(u.protocol) && u.origin !== location.origin;
                } catch (e) { return false; }
            }

            function useFallback(img) {
                var src = img.currentSrc || img.getAttribute('src') || '';
                if (img.dataset.imgFallback || !isProductImage(src)) return;
                img.dataset.imgFallback = '1';   // only once per image — no error loop
                img.removeAttribute('srcset');
                img.src = placeholder;
            }

            // 'error' doesn't bubble, so listen in the capture phase on the document.
            document.addEventListener('error', function (e) {
                if (e.target && e.target.tagName === 'IMG') useFallback(e.target);
            }, true);

            // Images that had already failed before this ran (e.g. restored from cache).
            window.addEventListener('load', function () {
                document.querySelectorAll('img').forEach(function (img) {
                    if (img.complete && img.naturalWidth === 0) useFallback(img);
                });
            });
        })();
    </script>

</head>

<body>

    @include('front.layout.header')
     <!-- Content -->
    @yield('content')
    <!-- / Content -->

    {{-- <!-- Footer --> --}}
    @include('front.layout.footer')

    <div class="bs-toast toast toast-placement-ex m-2 bottom-0 start-0" role="alert" aria-live="assertive"
        aria-atomic="true" data-bs-delay="2000">
        <div class="toast-header">
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">

        </div>
    </div>

    <script>
        function openSearch() {
        document.querySelector(".header_midrow").classList.toggle("search_active");}
    </script>

    <script>
        function toggleMenu() {
            document.querySelector(".header_bottomrow").classList.toggle("menu_active");
        }
    </script>

    <!-- script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/dist/js/slick.min.js') }}"></script>
    <script src="{{ asset('customjs/js/select2.min.js') }}"></script>

    <!-- Global Search Suggestions -->
    <script>
        (function () {
            var searchDebounce;
            var MIN_LEN = 2;

            function escapeHtml(str) {
                return $('<div>').text(str == null ? '' : str).html();
            }

            function renderSuggestions(container, data, query) {
                var html = '';

                if (data.categories && data.categories.length) {
                    html += '<div class="suggestion_group"><h6>Categories</h6><ul>';
                    data.categories.forEach(function (cat) {
                        html += '<li><a href="' + cat.url + '">' + escapeHtml(cat.title) + '</a></li>';
                    });
                    html += '</ul></div>';
                }

                if (data.products && data.products.length) {
                    html += '<div class="suggestion_group"><h6>Products</h6><ul>';
                    data.products.forEach(function (p) {
                        html += '<li><a href="' + p.url + '">' +
                            '<img src="' + p.image + '" alt="">' +
                            '<span>' + escapeHtml(p.title) + '</span>' +
                            (p.price ? '<b>' + escapeHtml(p.price) + '</b>' : '') +
                            '</a></li>';
                    });
                    html += '</ul></div>';
                }

                if (!html) {
                    html = '<div class="suggestion_empty">No results for &ldquo;' + escapeHtml(query) + '&rdquo;</div>';
                }

                container.html(html).addClass('active');
            }

            $(document).on('input', '.global_search_input', function () {
                var input = $(this);
                var query = input.val().trim();
                var container = input.closest('.serach_box').find('.search_suggestions');

                clearTimeout(searchDebounce);

                if (query.length < MIN_LEN) {
                    container.removeClass('active').empty();
                    return;
                }

                searchDebounce = setTimeout(function () {
                    $.ajax({
                        url: "{{ route('search.suggestions') }}",
                        type: 'GET',
                        data: { q: query },
                        success: function (response) {
                            // Ignore stale responses if the user kept typing
                            if (!response.error && query === input.val().trim()) {
                                renderSuggestions(container, response, query);
                            }
                        }
                    });
                }, 350);
            });

            $(document).on('focus', '.global_search_input', function () {
                var input = $(this);
                if (input.val().trim().length >= MIN_LEN) {
                    input.closest('.serach_box').find('.search_suggestions').addClass('active');
                }
            });

            // Close the dropdown when clicking outside the search box
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.serach_box').length) {
                    $('.search_suggestions').removeClass('active').empty();
                }
            });

            $(document).on('keydown', '.global_search_input', function (e) {
                if (e.key === 'Escape') {
                    $(this).closest('.serach_box').find('.search_suggestions').removeClass('active').empty();
                }
            });

            // Don't submit an empty search
            $(document).on('submit', '.global_search_form', function (e) {
                var input = $(this).find('.global_search_input');
                if (!input.val().trim()) {
                    e.preventDefault();
                }
            });
        })();
    </script>

    <script>
        $(".select2").select2();

        /*$('.single-modal-select').select2({
            dropdownParent: $('#setupShop')
        });

        $(".single-select_order").on("select2:select", function (evt) {
            let element = evt.params.data.element;
            let $element = $(element);

            $element.detach();
            $(this).append($element);
            $(this).trigger("change");
        });*/
    </script>

    <script src="{{ asset('assets/dist/js/custom.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('customjs/alert.js') }}"></script>
    <script src="{{ asset('assets/js/ui-toasts.js') }}"></script>

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

        /*$(document).on("click", ".confirm-delete", function() {
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
        });*/
        
        $(document).on("click", ".confirm-delete", function() {
            var link = $(this).data("href");
            Swal.fire({
                title: 'Are you sure you want to Delete ?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                confirmButtonColor: "#8486e3",
            }).then((result) => {
                if (result.isConfirmed) {
                    
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

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <!-- Login Js -->
    <script type="text/javascript">
        $(document).on("submit", "#LoginFromID", function(e) {

            $('#LoginFromID .input_fields').removeClass('has-error');
            $('#LoginFromID .help-block').html('');

            e.preventDefault();
            var formData = new FormData(this);
            $('.error_msg').text("");
            $.ajax({
                type: "POST",
                url: "{{ route('user.login') }}",
                datatype: 'JSON',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function(result){
                   jQuery('#LoginFromID #common_loader').show();
                },
                success: function(response) {
                    jQuery('#LoginFromID #common_loader').hide();
                    if (response.error) {
                        danger_msg(response.message);
                        var i = 1;
                        jQuery.each( response.data, function( index, value ) {
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
                        success_msg(response.message);
                        setTimeout(() => {
                            //window.location.href = "{{ route('user.dashboard') }}";
                            window.location.reload(1);
                        }, 1000);
                    }
                }
            });
            return false;
        });
    </script>

    <!-- Product Detail Jquery -->
    <script>
        $('body').on('click', '.variant_group .size-option input', function(e) {
            //e.preventDefault();
            $(this).closest('.variant_group').find('.size-option label').removeClass('active');
            $(this).closest('.size-option').find('label').addClass('active');
            var label_text = $(this).closest('.size-option').find('label').text();
            console.log('label_text', label_text );
            $(this).closest('.variant_group').find('.sizelabel p span').text(': '+ label_text);
            get_variable_product_price();

        });

        function get_variable_product_price() {

            let checked1 = $('input[name="variant_name1"]:checked');
            let checked2 = $('input[name="variant_name2"]:checked');

            let hasVariant1 = $('input[name="variant_name1"]').length > 0;
            let hasVariant2 = $('input[name="variant_name2"]').length > 0;

            // IF BOTH VARIANT GROUPS EXIST, BOTH MUST BE SELECTED
            if (hasVariant1 && !checked1.length) {
                console.log('Waiting: variant_name1 not selected yet');
                //reset_price_display();
                return;
            }

            if (hasVariant2 && !checked2.length) {
                console.log('Waiting: variant_name2 not selected yet');
                //reset_price_display();
                return;
            }

            let product_id  = $('#product_id').val();
            let product_qty = parseInt($('.qty').val()) || 1;

            let postData = {
                product_id:  product_id,
                product_qty: product_qty,
            };

            if (checked1.length) {
                postData.variant_name1 = checked1.val();
                //postData.attribute_id1 = checked1.data('attribute-id');
            }

            if (checked2.length) {
                postData.variant_name2 = checked2.val();
                //postData.attribute_id2 = checked2.data('attribute-id');
            }

            //console.log('Both selected — fetching price:', postData);

            $.ajax({
                type: "POST",
                url:  "{{ route('variable-product-price') }}",
                data: postData,
                beforeSend: function () {
                    //$('.price_display').html('<small>Loading...</small>');
                },
                success: function (response) {
                    if (!response.error) {
                        // UPDATE PRICE ON PAGE
                        $('#variable_price_id').html(response.html);

                        // UPDATE STOCK STATUS
                        if (response.stock !== undefined) {
                            $('.stock_display').text(response.stock > 0 ? 'In Stock' : 'Out of Stock');
                        }

                        // DISABLE ADD TO CART / BUY NOW WHEN THE SELECTED COMBINATION
                        // HAS NO STOCK OR NO PRICE
                        var available = (response.stock > 0) && (response.price > 0);
                        toggleAddToCartAvailability(available);
                    } else {
                        //reset_price_display();
                        toggleAddToCartAvailability(false);
                    }
                }
            });
        }

        // Enable/disable the Add to Cart & Buy Now buttons on the product detail page
        function toggleAddToCartAvailability(isAvailable) {
            var $cartBtn = $('.actions .cart_btn');
            var $buyBtn  = $('.actions .buy_btn');

            // Swap the button's leading text node only — leaves the <img> icon inside it intact
            $cartBtn.contents().filter(function () { return this.nodeType === 3; }).remove();
            $cartBtn.prepend(isAvailable ? 'Add to Cart' : 'Out of Stock');

            if (isAvailable) {
                $cartBtn.removeClass('disabled').removeAttr('aria-disabled');
                $buyBtn.removeClass('disabled').removeAttr('aria-disabled');
            } else {
                $cartBtn.addClass('disabled').attr('aria-disabled', 'true');
                $buyBtn.addClass('disabled').attr('aria-disabled', 'true');
            }
        }

        function reset_price_display() {
            // SHOW DEFAULT PRICE RANGE WHEN COMBINATION NOT YET SELECTED
            $('.price_display').html($('.default_price').html());
        }
    </script>

    <!--  -->
    <script>
        $(document).ready(function () {
            // INCREMENT
            $('body').on('click', '.plus', function () {
                let qtyInput = $(this).siblings('.qty');
                let currentQty = parseInt(qtyInput.val()) || 1;
                qtyInput.val(currentQty + 1);
            });

            // DECREMENT
            $('body').on('click', '.minus', function () {
                let qtyInput = $(this).siblings('.qty');
                let currentQty = parseInt(qtyInput.val()) || 1;
                // MINIMUM 1
                if (currentQty > 1) {
                    qtyInput.val(currentQty - 1);
                }
            });
        });
    </script>

    <!-- Cart Jquery -->
    <script>
         // Clear error on variant change
        $('body').on('change', '.variant_group .size-option input[type="radio"]', function () {
            $(this)
                .closest('.variant_group')
                .find('.variant_error')
                .remove();
        });

        // Shared by Add to Cart & Buy Now — posts the current product/variant/qty
        // selection to the cart, then hands the response to the caller's callback
        // (only called on success; errors are handled here the same way for both).
        function addToCartRequest(triggerEl, onSuccess) {
            let $trigger = $(triggerEl);

            let product_id  = $trigger.attr('data-id') || $('#product_id').val();
            let product_qty = parseInt($('.qty').val()) || 1;

            // Build post data — only include variant fields if they exist in DOM
            let postData = {
                product_id:  product_id,
                product_qty: product_qty,
            };

            // VARIANT 1
            let checked1 = $('input[name="variant_name1"]:checked');
            if ($('input[name="variant_name1"]').length) {
                postData.variant_name1 = checked1.length ? checked1.val() : '';
            }

            // VARIANT 2
            let checked2 = $('input[name="variant_name2"]:checked');
            if ($('input[name="variant_name2"]').length) {
                postData.variant_name2 = checked2.length ? checked2.val() : '';
            }

            let _this = $trigger.closest('.product_card');

            $.ajax({
                type: "POST",
                url:  "{{ route('add-to-cart') }}",
                data: postData,
                beforeSend: function () {
                    _this.find('.common_loader').show();
                    $('.variant_error').remove(); // Clear old errors before new request
                },
                success: function (response) {
                    _this.find('.common_loader').hide();

                    if (response.error) {
                        danger_msg(response.message);

                        // Show variant validation errors under correct .variant_group
                        if (response.data) {
                            $.each(response.data, function (key, value) {

                                // Only show errors for variant_name keys, not attribute_id keys
                                if (key.startsWith('variant_name')) {
                                    $('input[name="' + key + '"]')
                                        .closest('.variant_group')
                                        .append(
                                            '<span class="variant_error text-danger d-block mt-1">' +
                                            value[0] +
                                            '</span>'
                                        );
                                }
                            });
                        }

                        if (response.redirect) {
                            setTimeout(function () {
                                window.location.reload();
                            }, 2000);
                        }

                    } else {
                        onSuccess(response);
                    }
                }
            });
        }

        $('body').on('click', '.AddToCartAction', function (e) {
            e.preventDefault();

            if ($(this).hasClass('disabled')) {
                return;
            }

            addToCartRequest(this, function (response) {
                success_msg(response.message);
                $('.cart_itemstyle').trigger('click');
                $('#TotalCartID').text(response.cart_count);
                $('#sidebar-cart-items').html(response.html);
            });
        });

        // Buy Now — add to cart, then go straight to checkout
        $('body').on('click', '.BuyNowAction', function (e) {
            e.preventDefault();

            if ($(this).hasClass('disabled')) {
                return;
            }

            addToCartRequest(this, function (response) {
                $('#TotalCartID').text(response.cart_count);
                window.location.href = "{{ route('checkout') }}";
            });
        });

        function refreshCart(response) {
            // Cart page table rows
            if (response.items_html) {
                $('#cart-items-wrapper').html(response.items_html);
            }
            // Cart page totals panel
            if (response.totals_html) {
                $('#cart-totals-wrapper').html(response.totals_html);
            }
            // Sidebar cart items
            if (response.sidebar_html) {
                $('#sidebar-cart-items').html(response.sidebar_html);
            }
            // Cart count badge (header/sidebar)
            if (typeof response.cart_count !== 'undefined') {
                $('#TotalCartID').text(response.cart_count);
            }
        }

        // Update Cart
        $('#update-cart-btn').on('click', function (e) {
            e.preventDefault();
            let cart_ids = $('input[name="cart_id[]"]').map((i, el) => $(el).val()).get();
            let cart_qtys = $('input[name="cart_qty[]"]').map((i, el) => $(el).val()).get();

            /*$.post('{{ route("cart.update") }}', { cart_id: cart_ids, cart_qty: cart_qtys }, function (res) {
                if (res.success) refreshCart(res);
            });*/

            $.ajax({
                type: "POST",
                url:  "{{ route('cart.update') }}",
                data: { 
                    cart_id: cart_ids, 
                    cart_qty: cart_qtys 
                },
                beforeSend: function () {
                    $('#common_loader').show();
                },
                success: function (response) {
                    $('#common_loader').hide();
                    if (!response.error) {                    
                        success_msg(response.message);
                        refreshCart(response);
                    } else {
                         danger_msg(response.message);
                    }
                }
            });
        });

        // Delete single item
        $(document).on('click', '.delete_icon', function () {
            let cart_id = $(this).attr('cart-id');
            /*$.post('{{ route("cart.delete") }}', { cart_id: cart_id }, function (res) {
                if (res.success) refreshCart(res);
            });*/

            $.ajax({
                type: "POST",
                url:  "{{ route('cart.delete') }}",
                data: { 
                    cart_id: cart_id 
                },
                beforeSend: function () {
                    $('#common_loader').show();
                },
                success: function (response) {
                    $('#common_loader').hide();
                    if (!response.error) {                    
                        success_msg(response.message);
                        refreshCart(response);
                    } else {
                         danger_msg('Something want wrong');
                    }
                }
            });
        });

        // Clear all Cart Item
        $('#clear-cart-btn').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url:  "{{ route('cart.clear') }}",
                beforeSend: function () {
                    $('#common_loader').show();
                },
                success: function (response) {
                    $('#common_loader').hide();
                    if (!response.error) {
                        success_msg(response.message);
                        refreshCart(response);
                    } else {
                         danger_msg('Something want wrong');
                    }
                }
            });
        });

        // Apply / remove coupon on the cart page - delegated because
        // #cart-totals-wrapper's contents are replaced by refreshCart() on every
        // cart action, so a direct .on('click') binding would stop firing after
        // the first re-render.
        $(document).on('click', '#applyCouponBtn', function (e) {
            e.preventDefault();
            var code = $('#coupon_code').val().trim();
            if (!code) {
                $('#couponMessage').css('color', 'red').text('Please enter a coupon code.').show();
                return;
            }
            $.ajax({
                type: "POST",
                url:  "{{ route('cart.apply-coupon') }}",
                data: { code: code },
                beforeSend: function () {
                    $('#common_loader').show();
                },
                success: function (response) {
                    $('#common_loader').hide();
                    refreshCart(response);
                    if (response.error) {
                        danger_msg(response.message);
                    } else {
                        success_msg(response.message);
                    }
                },
                error: function () {
                    $('#common_loader').hide();
                    danger_msg('Server error. Please try again.');
                }
            });
        });

        $(document).on('click', '#removeCouponBtn', function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url:  "{{ route('cart.remove-coupon') }}",
                beforeSend: function () {
                    $('#common_loader').show();
                },
                success: function (response) {
                    $('#common_loader').hide();
                    refreshCart(response);
                },
                error: function () {
                    $('#common_loader').hide();
                    danger_msg('Server error. Please try again.');
                }
            });
        });

        $(document).on('keypress', '#coupon_code', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#applyCouponBtn').trigger('click');
            }
        });
    </script>


    <script>
        // Wishlist Toggle — same pattern as AddToCartAction
        $('body').on('click', '.WishlistToggle', function(e) {
            e.preventDefault();

            let btn         = $(this);
            let icon        = btn.find('.wishlist-icon');
            let product_id  = btn.attr('data-id');
            let isWishlisted = btn.attr('data-wishlisted') == '1';
            let _this       = btn.closest('.product_card');

            const heartFilledIcon    = "{{ asset('assets/dist/images/heart_filled.svg') }}";
            const heartEmptyIcon     = "{{ asset('assets/dist/images/cart_likeicon.svg') }}";

            $.ajax({
                type: 'POST',
                url:  "{{ route('add-to-wishlist') }}",
                data: {
                    //_token:     $('meta[name="csrf-token"]').attr('content'),
                    product_id: product_id,
                },
                beforeSend: function() {
                    _this.find('.common_loader').show();
                },
                success: function(response) {
                    _this.find('.common_loader').hide();
                    if (response.error) {
                        danger_msg(response.message);
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        }
                    } else {
                        if (response.wishlisted) {
                            icon.attr('src', heartFilledIcon);
                            btn.attr('data-wishlisted', '1');
                        } else {
                            icon.attr('src', heartEmptyIcon);
                            btn.attr('data-wishlisted', '0');
                        }
                        success_msg(response.message);

                        // Update wishlist count in header if you have one
                        /*if (typeof response.wishlist_count !== 'undefined') {
                            $('#TotalWishlistID').text(response.wishlist_count);
                        }*/
                    }
                },
                error: function() {
                    _this.find('.common_loader').hide();
                    danger_msg('Something went wrong!');
                }
            });
        });
    </script>

    <!-- Country/State/City Jquery -->
    <script type="text/javascript">
        //country_id
        $(document).ready(function () { 
            $(document).on("change", '#country_id', function() {
                select_states($(this).val());
            });
        });

        //state_id
        $(document).ready(function () { 
            $(document).on("change", '#state_id', function() {
                select_cities($(this).val());
            });
        });

        function select_states(country_id,child='0'){
            var label_title = "City";
            label_title = "State";
            $.ajax({
                url: "{{ route('front.get_state_by_country_id') }}",
                type: 'post',
                data: {
                    'title': label_title,
                    'country_id': country_id,
                    'child': child,
                    "_token": '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#state_id').html(response);
                }
            });
        }

        function select_cities(state_id,child='0'){
            var label_title = "City";
            var country_id = $('#country_id').val();
            $.ajax({
                url: "{{ route('front.get_city_by_state_id') }}",
                type: 'post',
                data: {
                    'title': label_title,
                    'country_id': country_id,
                    'state_id': state_id,
                    'child': child,
                    "_token": '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#city_id').html(response);
                }
            });
        }

        
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


    <!-- DatePicker -->
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
    <script>
        $(document).ready(function(){
            var today = new Date();
            // FROM DATE
            $("#filter_startdate").datepicker({
                maxDate: today, // ✅ Disable future dates
                onSelect: function (selected) {
                    var dt = new Date(selected);

                    // ✅ to_date: minDate = selected from_date, maxDate = today
                    $("#filter_enddate").datepicker("option", "minDate", dt);
                    $("#filter_enddate").datepicker("option", "maxDate", today);

                    // ✅ Clear to_date if it's now out of range
                    var currentEndDate = $("#filter_enddate").datepicker("getDate");
                    if (currentEndDate && (currentEndDate < dt || currentEndDate > today)) {
                        $("#filter_enddate").val("");
                    }
                }
            });

            // TO DATE
            $("#filter_enddate").datepicker({
                maxDate: today, // ✅ Disable future dates
                onSelect: function (selected) {
                    var dt = new Date(selected);

                    // ✅ from_date: maxDate should not exceed selected to_date
                    $("#filter_startdate").datepicker("option", "maxDate", dt);

                    // ✅ Clear from_date if it's now out of range
                    var currentStartDate = $("#filter_startdate").datepicker("getDate");
                    if (currentStartDate && currentStartDate > dt) {
                        $("#filter_startdate").val("");
                    }
                }
            });
        });
    </script>

    @yield('script')
    
</body>

</html>
