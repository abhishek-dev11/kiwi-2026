@extends('front.layout.master')
@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="{{ route('shop') }}"> All Category</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="allCategory_mainsec @if ( count($get_products) > 0 ) single_categorypg @endif">
    <div class="container">
        <div class="row">

            <div class="col-12 col-md-3 col-lg-3">
                <div class="filter_left">
                    <h4>Filter</h4>
                    <h5>All Category</h5>
                    <ul class="category_name">
                        <li><a href="#">Apparel</a></li>
                        <li><a href="#">Cafe</a></li>
                        <li><a href="#">Hygiene</a></li>
                        <li><a href="#">Office Furniture</a></li>
                        <li><a href="#">Packaging</a></li>
                        <li><a href="#">Promotional</a></li>
                        <li><a href="#">Safety</a></li>
                        <li><a href="#">Stationery</a></li>
                        <li><a href="#">Trade Events</a></li>
                        <li><a href="#">Tools</a></li>
                    </ul>
                    <ul class="category_status">
                        <li><a href="#">Price</a></li>
                        <li><a href="#">Stock Status</a></li>
                        <li><a href="#">Filter by size</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-12 col-md-9 col-lg-9">
                <div class="filter_right">

                    @if ( count($categories_list) > 0 )
                        <div class="category_toprow">
                            <div class="category_left">
                                <h2>All Category</h2>
                                <p>9 Results found</p>
                            </div>
                            <div class="select_category">
                                <select class="category_dropdown">
                                    <option value="">Sort by</option>
                                    <option value="apparel">Apparel</option>
                                    <option value="cafe">Cafe</option>
                                    <option value="office">Office Furniture</option>
                                    <option value="trade">Trade Events</option>
                                    <option value="hygiene">Hygiene</option>
                                </select>
                            </div>
                        </div>

                        <div class="see_category"> 
                            @foreach ($categories_list as $key => $value)
                                <div class="categories_filter">
                                    <div class="category_img">
                                        <a href="">
                                            <img
                                            src="{{ $value['image'] != '' ? get_image_upload('Category/'.$value['image']) : asset('placeholder/category/image.png') }}">
                                        </a>
                                    </div>
                                    <h5>
                                        <a href="">
                                            {{ ($value['title']) }} (<span>{{($value['count'])}}</span>)
                                        </a>
                                    </h5>
                                </div>
                            @endforeach
                        </div>  
                    @else
                        @if ( count($get_products) > 0 )
                            <div class="selling_secondrow single_categorycart" >
                                @foreach ($get_products as $key => $value)
                                    <div class="product_card">
                                        <div class="product_singlecatimg">
                                            <div class="add_favourite">
                                                <div class="like_icon">
                                                    <a href="#"><img src="{{ asset('assets/dist/images/cart_likeicon.svg') }}"></a>
                                                </div>
                                            </div>
                                            <div class="product_img">
                                                <a href="#">
                                                    <img src="{{ asset('assets/dist/images/signlecategory_img.png') }}">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="product_singleinfo">
                                            <div class="product_info">
                                                <h5>{{ ($get_category->title) }}</h5>
                                                <a href="#" style="text-decoration: none;">
                                                    <h3>{{ ($value->title) }}</h3>
                                                </a>
                                                <p>{{ ($value->short_decripation) }}</p>
                                            </div>
                                            <div class="product_price">
                                                <div class="change_price">
                                                    <p>$40</p>
                                                    <h6><span>$</span>66.00</h6>
                                                </div>
                                                <div class="add_tocartbtn">
                                                    <a href="#">Add to Card</a>
                                                    <div class="btncart_icon">
                                                        <a href="#">
                                                            <img src="{{ asset('assets/dist/images/addtocart-icon.svg') }}">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>    
                                    </div>
                                @endforeach
                            </div>

                            <!-- pagination -->
                            <!-- <div class="pagination_box">
                                <ul class="pagination">
                                    <li class="page_prev"><a href="#"><img src="./dist/images/pagination_arrow.svg"></a></li>
                                    <li><a href="#" class="page active">01</a></li>
                                    <li><a href="#" class="page">02</a></li>
                                    <li><a href="#" class="page">03</a></li>
                                    <li><a href="#" class="page">04</a></li>
                                    <li class="page_next"><a href="#"><img src="./dist/images/pagination_arrow.svg"></a></li>
                                </ul>
                            </div> -->
                            <?php 
                                /*echo "<pre>";
                                print_r($get_products);
                                echo "</pre>";*/
                            ?>
                        @endif
                    @endif


                </div>
            </div>
        </div>
    </div>
</div>

<div class="top_sellingsection">
    <div class="container">
        <div class="top_selltoprow">
            <h2 class="common_heading">Top Selling <span>Products</span></h2>
            <div class="category_allbtn common_btn">
                <a href="./category.html">Browse</a>
            </div>
        </div>
        <div class="selling_secondrow selling_prodslider dots_none">
            <!-- CARD 1 -->
            <div class="slide">
                <div class="product_card">

                    <div class="add_favourite">
                        <div class="like_icon">
                            <a href="#"><img src="{{ asset('assets/dist/images/cart_likeicon.svg') }}"></a>
                        </div>
                    </div>
                    <div class="product_img">
                        <a href="#"><img src="{{ asset('assets/dist/images/cart_product1.png') }}"></a>
                    </div>
                    <div class="product_info">
                        <h5>Riggers</h5>
                        <a href="#"><h3>Armour Leather Full Split Rigger Glove LGRIG</h3></a>
                        <p>A full leather economy glove made form...</p>
                    </div>
                    <div class="product_price">
                        <h6><span>$</span>66.00</h6>
                        <div class="add_tocartbtn">
                            <a href="#">Add to Card</a>
                            <div class="btncart_icon">
                                <a href="#"><img src="{{ asset('assets/dist/images/addtocart-icon.svg') }}"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 2 -->
            <div class="slide">
                <div class="product_card">
                    <div class="add_favourite">
                        <div class="like_icon">
                            <a href="#"><img src="{{ asset('assets/dist/images/cart_likeicon.svg') }}"></a>
                        </div>
                    </div>
                    <div class="product_img">
                        <a href="#"><img src="{{ asset('assets/dist/images/cart_product2.png') }}"></a>
                    </div>
                    <div class="product_info">
                        <h5>Leisure & Outdoors</h5>
                        <a href="#"><h3>Keepsake Picnic <br>Blanket</h3></a>
                        <p>A full leather economy glove made form...</p>
                    </div>
                    <div class="product_price">
                        <h6><span>$</span>66.00</h6>
                        <div class="add_tocartbtn">
                            <a href="#">Add to Card</a>
                            <div class="btncart_icon">
                                <a href="#"><img src="{{ asset('assets/dist/images/addtocart-icon.svg') }}"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 3 -->
            <div class="slide">
                <div class="product_card">
                    <div class="add_favourite">
                        <div class="like_icon">
                            <a href="#"><img src="{{ asset('assets/dist/images/cart_likeicon.svg') }}"></a>
                        </div>
                    </div>
                    <div class="product_img">
                        <a href="#"><img src="{{ asset('assets/dist/images/cart_product3.png') }}"></a>
                    </div>
                    <div class="product_info">
                        <h5>Drinkware</h5>
                        <a href="#"><h3>Atlas Shaker <br>400ml</h3></a>
                        <p>A full leather economy glove made form...</p>
                    </div>
                    <div class="product_price">
                        <h6><span>$</span>66.00</h6>
                        <div class="add_tocartbtn">
                            <a href="#">Add to Card</a>
                            <div class="btncart_icon">
                                <a href="#"><img src="{{ asset('assets/dist/images/addtocart-icon.svg') }}"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 4 -->
            <div class="slide">
                <div class="product_card">
                    <div class="add_favourite">
                        <div class="like_icon">
                            <a href="#"><img src="{{ asset('assets/dist/images/cart_likeicon.svg') }}"></a>
                        </div>
                    </div>
                    <div class="product_img">
                        <a href="#"><img src="{{ asset('assets/dist/images/cart_product4.png') }}"></a>
                    </div>
                    <div class="product_info">
                        <h5>Drinkware</h5>
                        <a href="#"><h3>Chai <br>Teapot</h3></a>
                        <p>A full leather economy glove made form...</p>
                    </div>
                    <div class="product_price">
                        <h6><span>$</span>66.00</h6>
                        <div class="add_tocartbtn">
                            <a href="#">Add to Card</a>
                            <div class="btncart_icon">
                                <a href="#"><img src="{{ asset('assets/dist/images/addtocart-icon.svg') }}"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD 5 -->
            <div class="slide">
                <div class="product_card">
                    <div class="add_favourite">
                        <div class="like_icon">
                            <a href="#"><img src="{{ asset('assets/dist/images/cart_likeicon.svg') }}"></a>
                        </div>
                    </div>
                    <div class="product_img">
                        <a href="#"><img src="{{ asset('assets/dist/images/cart_product1.png') }}"></a>
                    </div>
                    <div class="product_info">
                        <h5>Riggers</h5>
                        <a href="#"><h3>Armour Leather Full Split Rigger Glove LGRIG</h3></a>
                        <p>A full leather economy glove made form...</p>
                    </div>
                    <div class="product_price">
                        <h6><span>$</span>66.00</h6>
                        <div class="add_tocartbtn">
                            <a href="#">Add to Card</a>
                            <div class="btncart_icon">
                                <a href="#"><img src="{{ asset('assets/dist/images/addtocart-icon.svg') }}"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection