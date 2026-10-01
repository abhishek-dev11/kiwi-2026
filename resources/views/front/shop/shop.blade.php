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

<div class="allCategory_mainsec single_categorypg">
    <div class="container">
        <div class="row">

            <div class="col-12 col-md-3 col-lg-3">
                <div class="filter_left">
                    <h4>Filter</h4>
                    <h5>All Category</h5>
                    <ul class="category_name">
                        @if ( count($categories_list) > 0 )
                            @foreach ($categories_list as $key => $value)
                                <li> 
                                    <a href="{{ route('mid-level-category', $value['slug']) }}">
                                        {{ ($value['title']) }}
                                    </a>
                                </li>
                            @endforeach                        
                        @endif
                    </ul>
                    <!-- <ul class="category_status">
                        <li><a href="#">Price</a></li>
                        <li><a href="#">Stock Status</a></li>
                        <li><a href="#">Filter by size</a></li>
                    </ul> -->
                </div>
            </div>

            <div class="col-12 col-md-9 col-lg-9">
                <div class="filter_right">
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
                        @if ( count($categories_list) > 0 )
                            @foreach ($categories_list as $key => $value)
                                <div class="categories_filter">
                                    <div class="category_img">
                                        <a href="{{ route('mid-level-category', $value['slug']) }}">
                                            <img
                                            src="{{ $value['image'] != '' ? get_image_upload('Category/'.$value['image']) : asset('placeholder/category/image.png') }}">
                                        </a>
                                    </div>
                                    <h5>
                                        <a href="{{ route('mid-level-category', $value['slug']) }}">
                                            {{ ($value['title']) }} (<span>{{($value['count'])}}</span>)
                                        </a>
                                    </h5>
                                </div>
                            @endforeach
                        @else
                            
                        @endif

                        <!-- <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img1.png') }}"></a>
                            </div>
                            <h5><a href="#">Apparel (<span>1370</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img2.png') }}"></a>
                            </div>
                            <h5><a href="#">Cafe (<span>151</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img3.png') }}"></a>
                            </div>
                            <h5><a href="#">Hygiene (<span>591</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img4.png') }}"></a>
                            </div>
                            <h5><a href="#">Office Furniture (<span>1</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img6.png') }}"></a>
                            </div>
                            <h5><a href="#">Packaging (<span>156</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img7.png') }}"></a>
                            </div>
                            <h5><a href="#">Promotional (<span>2247</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img8.png') }}"></a>
                            </div>
                            <h5><a href="#">Safety (<span>1132</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img9.png') }}"></a>
                            </div>
                            <h5><a href="#">Stationery (<span>5039</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img5.png') }}"></a>
                            </div>
                            <h5><a href="#">Trade Events (<span>221</span>)</a></h5>
                        </div>

                        <div class="categories_filter">
                            <div class="category_img">
                                <a href="#"><img src="{{ asset('assets/dist/images/category_img10.png') }}"></a>
                            </div>
                            <h5><a href="#">Tools(<span>3520</span>)</a></h5>
                        </div> -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('front.partials.top-selling-products-alt')

@endsection