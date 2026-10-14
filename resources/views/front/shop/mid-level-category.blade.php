@extends('front.layout.master')
@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                @if( count($breadcrumbs_arrs) > 0 )
                    @foreach( $breadcrumbs_arrs as $key => $value )
                        <li><span>/</span></li>
                        <li >
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
                <div class="filter_left subcategory-style">
                    <div class="clear_alltop">
                        <h4>Filter</h4>
                        <a href="{{ route('shop') }}">Clear All</a>
                    </div>
                    <div class="category_cancelbtn">
                        <a href="{{ route('shop') }}">
                            <span class="active">{{ $get_category->title }}</span>
                            <img src="{{ asset('assets/dist/images/cancel_iconn.svg') }}">
                        </a>
                    </div>
                    
                    <h5>All Category</h5>

                    <div class="category_box">
                        <h4>Category</h4>
                        <ul class="category_list">
                            <li class="category">
                                <div class="category_header">
                                    <span><a href="{{ route('mid-level-category', $get_category->slug) }}" class="active">{{ $get_category->title }}</a></span>
                                    <span class="arrow rotate"><img src="{{ asset('assets/dist/images/category_arrow.svg') }}"></span>
                                </div>

                                <ul class="subcategory open">
                                    @if ( count($categories_list) > 0 )
                                        @foreach ($categories_list as $key => $value)
                                            <li class="subcategory_item">
                                                <div class="subcategory_header">
                                                    <span>
                                                        <a href="{{ route('bottom-level-category', 
                                                                [
                                                                    'parent_slug' => $value['parent_slug'],
                                                                    'child_slug' => $value['child_slug'],
                                                                ]) 
                                                            }}"  >
                                                            {{ ($value['title']) }} 
                                                        </a>
                                                    </span>
                                                    <span class="arrow">
                                                        <img src="{{ asset('assets/dist/images/category_arrow.svg') }}">
                                                    </span>
                                                </div>
                                                @php
                                                    $product_count = 0;
                                                    $get_bottom_categories = App\Models\Categories::whereNull('is_delete')->whereNotNull('top_level_category_id')->whereNotNull('mid_level_category_id')->orderBy('title', 'asc');
                                                    $get_bottom_category = App\Models\Categories::whereNull('is_delete')->where('slug', $value['child_slug'])->first();
                                                    if( $get_bottom_category ){
                                                        $get_bottom_categories->where('mid_level_category_id',$get_bottom_category->id);                                                        
                                                    }
                                                    $get_bottom_categories  = $get_bottom_categories->get();

                                                @endphp
                                                @if( count($get_bottom_categories) > 0  )
                                                    <ul class="child_list">
                                                        @foreach ($get_bottom_categories as $btc_key => $btc_value)
                                                            @php
                                                            $product_count = categoryDisplayCount($btc_value);
                                                            @endphp
                                                            <li class="active">
                                                                <a href="{{ route('products', 
                                                                        [
                                                                            'parent_slug' => $value['parent_slug'],
                                                                            'child_slug' => $value['child_slug'],
                                                                            'sub_child_slug' => $btc_value->slug,
                                                                        ]
                                                                    ) }}">
                                                                    {{ ($btc_value->title) }} 
                                                                    <span>({{ ($product_count) }})</span>
                                                                </a>
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
                </div>
            </div>

            <div class="col-12 col-md-9 col-lg-9">
                <div class="filter_right">
                    <div class="category_toprow">
                        <div class="category_left">
                            <h2>{{ ($get_category->title) }}</h2>
                            <p> {{ $show_products ? $get_products->total() : count($categories) }} Results found</p>
                        </div>
                        <div class="select_category">
                            <select class="category_dropdown">
                                <option value="">Sort by</option> 
                            </select>
                        </div>
                    </div>

                    @if ( $show_products )
                        @include('front.shop.search-product', [
                            'get_products' => $get_products,
                            'get_bottom_category' => $get_category,
                            'wishlistedIds' => $wishlistedIds,
                        ])
                    @else
                    <div class="see_category">
                        @if ( count($categories_list) > 0 )
                            @foreach ($categories_list as $key => $value)
                                <div class="categories_filter">
                                    <div class="category_img">
                                        <a href="{{ route('bottom-level-category', 
                                            [
                                                'parent_slug' => $value['parent_slug'],
                                                'child_slug' => $value['child_slug'],
                                            ]
                                        ) }}">
                                            <img
                                            src="{{ $value['image'] != '' ? get_image_upload('Category/'.$value['image']) : asset('placeholder/category/image.png') }}">
                                        </a>
                                    </div>
                                    <h5>
                                        <a href="{{ route('bottom-level-category', 
                                            [
                                                'parent_slug' => $value['parent_slug'],
                                                'child_slug' => $value['child_slug'],
                                            ]
                                        ) }}">
                                            {{ ($value['title']) }} (<span>{{($value['count'])}}</span>)
                                        </a>
                                    </h5>
                                </div>
                            @endforeach
                        @else
                            
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('front.partials.top-selling-products-alt')

@endsection
