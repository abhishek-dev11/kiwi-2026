<header class="header_secton">
    <div class="header_top">
        <div class="container">
            <div class="headtop_row">
                @php
                    $phone = $siteSettings['footer_phone_4'] ?: '0272132533';
                    $email = $siteSettings['footer_email_4'] ?: 'steve@kiwibusinessproducts.co.nz';
                @endphp
                <div class="headt_leftcol">
                    <div class="phone_info">
                        <div class="info_inner">
                            <img src="{{ asset('assets/dist/images/phone-icon.png') }}">
                            <a href="tel:{{ $phone }}">{{ $phone }}</a>
                        </div>
                    </div>
                    <span>|</span>
                    <div class="email_info">
                        <div class="info_inner">
                            <img src="{{ asset('assets/dist/images/email-icon.png') }}">
                            <a href="mailto:{{ $email }}">{{ $email }}</a>
                        </div>
                    </div>
                </div>
                <div class="headt_rightcol">
                    <ul class="social_icons">
                        <li><a href="{{ $siteSettings['facebook_link'] ?: '#' }}" target="_blank" rel="noopener"><img src="{{ asset('assets/dist/images/facebook-icon.svg') }}"></a></li>
                        <li><a href="{{ $siteSettings['instagram_link'] ?: '#' }}" target="_blank" rel="noopener"><img src="{{ asset('assets/dist/images/instagram-icon.svg') }}"></a></li>
                        <li><a href="{{ $siteSettings['linkedin_link'] ?: '#' }}" target="_blank" rel="noopener"><img src="{{ asset('assets/dist/images/linkdin-icon.svg') }}"></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="header_middle">
        <div class="container">
            <div class="header_midrow ">
                <div class="head_logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ $siteSettings['header_logo_url'] }}">
                    </a>
                </div>

                <div class="serach_box">
                    <form class="d-flex global_search_form" action="{{ route('search') }}" method="GET" autocomplete="off">
                        <input class="form-control global_search_input" type="search" name="q" value="{{ request('q') }}" placeholder="Search for products" id="GlobalSearchInput">
                        <button class="search_btn" type="submit">
                            <img src="{{ asset('assets/dist/images/search_svg.svg') }}" alt="search">
                        </button>
                        <div class="search_suggestions" id="GlobalSearchSuggestions"></div>
                    </form>
                </div>

                <div class="user_info">
                    <div class="search_iconphone" onclick="openSearch()">
                        <img src="{{ asset('assets/dist/images/search_svg.svg') }}" alt="search">
                    </div>

                    <div class="my_account">
                        <div class="account_img">
                            <img src="{{ asset('assets/dist/images/user_svg.svg') }}">
                        </div>
                        <div class="account_info">
                            @if(Auth::guard('customers')->check())
                                <p><a href="{{ route('user.dashboard') }}">My Account</a></p>
                                <h6><a href="{{ route('user.logout') }}">Logout</a></h6>
                            @else
                                <h6>
                                    @if (request()->is('user/login'))
                                        <a href="{{ route('user.login') }}">Login</a>
                                    @else
                                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                                    @endif
                                </h6>
                            @endif
                        </div>
                    </div>

                    <span>|</span>

                    <div class="shop_img">
                        @if(Auth::guard('customers')->check())
                            <a href="{{ route('user.wishlist') }}">
                                <img src="{{ asset('assets/dist/images/like_svg.svg') }}">
                            </a>
                        @else
                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <img src="{{ asset('assets/dist/images/like_svg.svg') }}">
                            </a>
                        @endif

                        <a class="cart_itemstyle" href="javascript:void(0);" onclick="toggleCart()">
                            <img class="carticon" src="{{ asset('assets/dist/images/cartsvg.svg') }}">
                            <div class="cart_items">
                                <span id="TotalCartID">{{ $cart_count }}</span>
                            </div>
                        </a>
                    </div> 

                </div>
            </div>
        </div>
    </div>

    <div class="header_bottom">
        <div class="container">
            <div class="header_bottomrow">

                <div class="dropdown_field">
                    <a href="#">
                        <div class="category_btn" onclick="toggleMegaMenu()">
                            <div class="hidemobile">Shop by Category</div>
                            <span class="arrow">
                                <img src="{{ asset('assets/dist/images/down_arrow.svg') }}">
                            </span>
                        </div>
                    </a>

                    <div class="mega_menu" id="megaMenu">
                        <div class="mega_container">

                            <ul class="mega_sidebar"> 

                                <li class="active" data-target="apparel">
                                    <a href="#">
                                        <div class="menu_name">
                                            <img src="{{ asset('assets/dist/images/Apparelmenuicon.svg') }}">
                                            <span class="product_name">Apparel</span>
                                        </div>
                                        <span>
                                            <img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}">
                                        </span>
                                    </a>
                                </li>

                                <li data-target="cafe">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/Cafemenuicon.svg') }}">
                                         <span class="product_name">Cafe</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>

                                <li data-target="hygiene">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/Hygieneicon.svg') }}">
                                         <span class="product_name">Hygiene</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>

                                <li data-target="office">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/Office-Furnitureicon.svg') }}">
                                         <span class="product_name">Office Furniture</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>

                                <li data-target="packaging">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/Packagingicon.svg') }}">
                                         <span class="product_name">Packaging</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>

                                <li data-target="promotional">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/promotional-menu.svg') }}">
                                         <span class="product_name">Promotional</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>

                                <li data-target="safety">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/Safetyicon.svg') }}">
                                         <span class="product_name">Safety</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>

                                <li data-target="stationery">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/stationery-menu.svg') }}">
                                         <span class="product_name">Stationery</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>

                                <li data-target="trade">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/trade-menu.svg') }}">
                                         <span class="product_name">Trade Events</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>

                                <li data-target="tools">
                                   <a href="">
                                      <div class="menu_name">
                                         <img src="{{ asset('assets/dist/images/tools.svg') }}">
                                         <span class="product_name">Tools</span>
                                      </div>
                                      <span><img src="{{ asset('assets/dist/images/megamenuarrow.svg') }}"></span>
                                   </a>
                                </li>
                            </ul>

                            <div class="mega_right">

                                <!-- APPAREL -->
                                <div class="mega_block active" id="apparel">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- CAFE -->
                                <div class="mega_block" id="cafe">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- hygiene -->
                                <div class="mega_block" id="hygiene">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- OFFICE -->
                                <div class="mega_block" id="office">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- packaging -->
                                <div class="mega_block" id="packaging">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- promotional -->
                                <div class="mega_block" id="promotional">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- safety -->
                                <div class="mega_block" id="">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- Stationery -->
                                <div class="mega_block" id="Stationery">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- Trade Events -->
                                <div class="mega_block" id="trade">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                                <!-- Tools -->
                                <div class="mega_block" id="tools">
                                    <div class="columns">
                                        <div class="column">
                                            <a href="#">Belts</a>
                                            <a href="#">Blouse</a>
                                            <a href="#">Cardigan</a>
                                            <a href="#">Headwear</a>
                                            <a href="#">Healthcare</a>
                                            <a href="#">Hi Vis</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Shorts</a>
                                            <a href="#">Track Pants</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Hoodie</a>
                                            <a href="#">Polar Fleece</a>
                                            <a href="#">Singlets</a>
                                            <a href="#">Hospitality</a>
                                            <a href="#">Skirts</a>
                                            <a href="#">Jackets</a>
                                            <a href="#">Pants</a>
                                            <a href="#">Pullover</a>
                                            <a href="#">Tees</a>
                                        </div>

                                        <div class="column">
                                            <a href="#">Overalls</a>
                                            <a href="#">Shirts</a>
                                            <a href="#">Thermal</a>
                                            <a href="#">Vest</a>
                                            <a href="#">Wet Weather</a>
                                            <a href="#">Socks</a>
                                        </div>
                                    </div>

                                    <div class="mega_image">
                                        <img src="{{ asset('assets/dist/images/menuimg.png') }}">
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                <div class="nav_menu">
                    <ul>
                        <li class="active"><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('shop') }}">Shop</a></li>
                        <li><a href="{{ route('aboutus') }}">About Us</a></li>
                        <li><a href="{{ route('contactus') }}">Contact Us</a></li>
                    </ul>
                </div>

                <div class="toggle_btn" onclick="toggleMenu()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="contact_btn">
                    <a href="{{ route('contactus') }}">Contact Us</a>
                </div>

            </div>
        </div>
    </div>            
</header>

@include('front.cart.cart-sidebar')