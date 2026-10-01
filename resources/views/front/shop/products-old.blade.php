@extends('front.layout.master')
@section('content')
@php
    $selected_category = $get_fourth_category ?? $get_bottom_category;
@endphp

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                @if( count($breadcrumbs_arrs) > 0 )
                    @foreach( $breadcrumbs_arrs as $key => $value )
                        <li><span>/</span></li>
                        <li>
                            <a href="{{ route($value['route'], $value['params']) }}"> {{ $value['title'] }}</a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>

<div class="allCategory_mainsec single_categorypg">
    <div class="container">
        <div class="row">

            <div class="col-12 col-md-3 col-lg-3">
                <div class="filter_left">
                    <form method="POST" class="relative_cls" action="{{ route('front.trade_signup') }}" enctype="multipart/form-data" id="FilterFormID">

                        @csrf
                        <input type="hidden" name="top_category_id" value="{{$get_top_category->id}}" >
                        <input type="hidden" name="mid_category_id" value="{{$get_mid_category->id}}" >
                        <input type="hidden" name="bottom_category_id" value="{{$selected_category->id}}" >

                        <div class="clear_alltop">
                            <h4>Filter</h4>
                            <a href="{{ route('shop') }}">Clear All</a>
                        </div>

                        <div class="category_cancelbtn">
                            <a href="{{ route('shop') }}">
                                <span class="active">{{ $get_top_category->title }}</span>
                                <img src="{{ asset('assets/dist/images/cancel_iconn.svg') }}">
                            </a>

                            
                            <a href="{{ route('mid-level-category', $get_top_category->slug) }}">
                                <span class="active">{{ $get_mid_category->title }}</span>
                                <img src="{{ asset('assets/dist/images/cancel_iconn.svg') }}">
                            </a>

                            <a href="{{ route('bottom-level-category',  [
                                        'parent_slug' => $get_top_category->slug,
                                        'child_slug' => $get_mid_category->slug,
                                    ]) 
                                }}">
                                <span class="active">{{ $selected_category->title }}</span>
                                <img src="{{ asset('assets/dist/images/cancel_iconn.svg') }}">
                            </a>
                        </div>

                        <div class="serach_box">
                            <div class="d-flex">
                                <input class="form-control" type="search" name="search_product" placeholder="Search products" id="SearchProductInput">
                                <button class="search_btn" type="button" onclick="openSearch()">
                                    <img src="{{ asset('assets/dist/images/search_svg.svg') }}" alt="search">
                                </button>
                            </div>
                        </div>

                        <h5>All Category</h5>
                        <div class="category_box">
                            <h4>Category</h4>
                            <ul class="category_list">
                                <li class="category">
                                    <div class="category_header">
                                        <span><a href="{{ route('mid-level-category', $get_top_category->slug) }}" class="active">{{ $get_top_category->title }}</a></span>
                                        <span class="arrow rotate"><img src="{{ asset('assets/dist/images/category_arrow.svg') }}"></span>
                                    </div>

                                    <ul class="subcategory open">
                                        @if ( count($top_categories_list) > 0 )
                                            @foreach ($top_categories_list as $key => $value)
                                                @php
                                                    $product_count = 0;
                                                    $get_bottom_categories = App\Models\Categories::whereNull('is_delete')->whereNotNull('top_level_category_id')->whereNotNull('mid_level_category_id')->whereNull('bottom_level_category_id')->orderBy('title', 'asc')->where('mid_level_category_id',$value['id']);
                                                    $get_bottom_categories  = $get_bottom_categories->get();
                                                @endphp

                                                <?php 
                                                    $active_cls = '';
                                                    $open_cls = '';
                                                    $rotate_cls = '';
                                                    if( $value['child_slug'] == $get_mid_category->slug ){
                                                        $active_cls = 'active';
                                                        $open_cls = 'open';
                                                        $rotate_cls = 'rotate';
                                                    }
                                                ?>

                                                <li class="subcategory_item">
                                                    <div class="subcategory_header">
                                                        <span>
                                                            <a href="{{ route('bottom-level-category', 
                                                                    [
                                                                        'parent_slug' => $value['parent_slug'],
                                                                        'child_slug' => $value['child_slug'],
                                                                    ]) 
                                                                }}" class="{{ $active_cls }}" >
                                                                {{ ($value['title']) }} 
                                                            </a>
                                                        </span>

                                                        @if( count($get_bottom_categories) > 0  )
                                                            <span class="arrow {{ $rotate_cls }}">
                                                                <img src="{{ asset('assets/dist/images/category_arrow.svg') }}">
                                                            </span>
                                                        @endif
                                                    </div>

                                                    
                                                    @if( count($get_bottom_categories) > 0  )
                                                        <ul class="child_list {{$open_cls}}">
                                                            @foreach ($get_bottom_categories as $btc_key => $btc_value)
                                                                @php
                                                                $get_fourth_categories = App\Models\Categories::whereNull('is_delete')->where('bottom_level_category_id', $btc_value->id)->orderBy('title', 'asc')->get();
                                                                $product_count = App\Models\Products::where(ConditionQuery())
                                                                    ->where(function ($query) use ($btc_value) {
                                                                        $query->where('category', $btc_value->id)
                                                                            ->orWhere('bottom_level_category_id', $btc_value->id);
                                                                    })
                                                                    ->distinct('id')
                                                                    ->count('id');
                                                                $bottom_active_cls = $btc_value->slug == $get_bottom_category->slug ? 'active' : '';
                                                                @endphp
                                                                <li class="{{ $bottom_active_cls }}">
                                                                    <a href="{{ route('products', 
                                                                            [
                                                                                'parent_slug' => $value['parent_slug'],
                                                                                'child_slug' => $value['child_slug'],
                                                                                'sub_child_slug' => $btc_value->slug,
                                                                            ]
                                                                        ) }}" class="{{ $bottom_active_cls }}">
                                                                        {{ ($btc_value->title) }} 
                                                                        <span>({{ ($product_count) }})</span>
                                                                    </a>
                                                                    @if(count($get_fourth_categories) > 0)
                                                                        <ul class="child_list {{ $bottom_active_cls ? 'open' : '' }}">
                                                                            @foreach($get_fourth_categories as $fourth_category)
                                                                                @php
                                                                                    $fourth_product_count = App\Models\Products::where(ConditionQuery())->where('category', $fourth_category->id)->count();
                                                                                    $fourth_active_cls = isset($get_fourth_category) && $get_fourth_category && $fourth_category->slug == $get_fourth_category->slug ? 'active' : '';
                                                                                @endphp
                                                                                <li class="{{ $fourth_active_cls }}">
                                                                                    <a href="{{ route('fourth-level-products', [
                                                                                            'parent_slug' => $value['parent_slug'],
                                                                                            'child_slug' => $value['child_slug'],
                                                                                            'sub_child_slug' => $btc_value->slug,
                                                                                            'fourth_slug' => $fourth_category->slug,
                                                                                        ]) }}" class="{{ $fourth_active_cls }}">
                                                                                        {{ $fourth_category->title }}
                                                                                        <span>({{ $fourth_product_count }})</span>
                                                                                    </a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif

                                                </li>
                                            @endforeach                        
                                        @endif
                                    </ul>
                                </li> 
                            </ul>
                        </div>
                        
                        <div class="filter_box">
                            <!-- PRICE -->
                            <h3>Price</h3>
                            <p id="priceValue">$0 - $500</p>

                            <div class="range-slider">
                                <div class="range-fill" id="rangeFill"></div>
                                <input type="range" id="minRange" name="min_price" min="0" max="100" value="0">
                                <input type="range" id="maxRange" name="max_price" min="0" max="500" value="500">
                            </div>
                            <hr>

                            <!-- STOCK -->
                            <div>
                                <h3>Stock Status</h3>
                                <label><input type="radio" name="stock_status" value="all_product" checked> All Product</label>
                                <label><input type="radio" name="stock_status" value="in_stock" > In Stock</label>
                                <label><input type="radio" name="stock_status" value="on_sale" > On Sale</label>
                            </div>
                            <hr>

                            <!-- SIZE -->
                            <!-- <div>
                                <h3>Filter By Size</h3>
                                <label><input type="checkbox" checked> 10 (108)</label>
                                <label><input type="checkbox"> 102 (103)</label>
                                <label><input type="checkbox"> 102R (6)</label>
                                <label><input type="checkbox"> 102S (3)</label>
                                <label><input type="checkbox"> 107 (106)</label>
                                <label><input type="checkbox"> 107S (4)</label>
                            </div> -->
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-md-9 col-lg-9">
                <div class="filter_right ">

                    @if ( !$show_products )
                        <div class="category_toprow">
                            <div class="category_left">
                                <h2>{{ ($selected_category->title) }}</h2>
                                <p>{{ count($categories_list) }} Results found</p>
                            </div>
                            <div class="select_category">
                                <select class="category_dropdown">
                                    <option value="">Sort by</option>
                                </select>
                            </div>
                        </div>

                        <div class="see_category">
                            @foreach ($categories_list as $key => $value)
                                <div class="categories_filter">
                                    <div class="category_img">
                                        <a href="{{ route('fourth-level-products',
                                            [
                                                'parent_slug' => $value['parent_slug'],
                                                'child_slug' => $value['child_slug'],
                                                'sub_child_slug' => $value['sub_child_slug'],
                                                'fourth_slug' => $value['fourth_slug'],
                                            ]
                                        ) }}">
                                            <img
                                            src="{{ $value['image'] != '' ? get_image_upload('Category/'.$value['image']) : asset('placeholder/category/image.png') }}">
                                        </a>
                                    </div>
                                    <h5>
                                        <a href="{{ route('fourth-level-products',
                                            [
                                                'parent_slug' => $value['parent_slug'],
                                                'child_slug' => $value['child_slug'],
                                                'sub_child_slug' => $value['sub_child_slug'],
                                                'fourth_slug' => $value['fourth_slug'],
                                            ]
                                        ) }}">
                                            {{ ($value['title']) }} (<span>{{($value['count'])}}</span>)
                                        </a>
                                    </h5>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="category_toprow">
                            <div class="category_left">
                                <h2>{{ ($selected_category->title) }}</h2>
                                <p> <span id="product_count_id"> </span> Results found</p>
                            </div>
                            <div class="select_category">
                                <select class="category_dropdown">
                                    <option value="">Sort by</option>
                                </select>
                            </div>
                        </div>

                        <div class="relative_cls" >
                            <div id="common_loader">
                                <div>
                                    <img src="{{ asset('assets/images/loader.gif') }}">
                                </div>
                            </div>
                            <div id="productData"> </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@include('front.partials.top-selling-products')

@endsection

@section('script')
<!-- Product Filter Jquery -->
<script>
    let searchTimer;
    function fetchProducts(page = 1)
    {
        let form = $('#FilterFormID')[0];
        let formData = new FormData(form);
        formData.append('page', page);
        $.ajax({
            type: "POST",
            url: "{{ route('product-filter') }}",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function(result){
               jQuery('#common_loader').show();
            },
            success: function(response)
            {
                jQuery('#common_loader').hide();
                if (!response.error) {
                    $('#productData').html(response.html);
                    $('#product_count_id').text(response.count)
                }
            }
        });
    }

    $("#SearchProductInput").on("input", function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            fetchProducts();
        }, 500);
    });

    // PRICE RANGE FILTER
    $("#minRange, #maxRange").on("input", function () {
        $('#minPriceText').text($('#minRange').val());
        $('#maxPriceText').text($('#maxRange').val());
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            fetchProducts();
        }, 500);

    });

    $('body').on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        fetchProducts(page);
    });
 
    $('body').on('change','.filter_box input[name="stock_status"]', function(e) {
      fetchProducts();
    });

    @if ( $show_products )
        fetchProducts();
    @endif
</script>
@endsection
