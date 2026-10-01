@extends('front.layout.master')
@section('content')

<div class="about_herosec">
    <div class="container">
        <div class="about_herorow">
            <h2>{{ $data['hero_title'] }}</h2>
            <h1>{{ $data['hero_subtitle'] }}</h1>
            <p>{!! $data['hero_text'] !!}</p>
        </div>
    </div>
</div>

<div class="ourcompany_sec">
    <div class="container">
        <div class="row">

            <div class="col-12 col-md-6 col-lg-6">
                <div class="ourcompany_left">

                    <div class="left_innercol">
                        <img src="{{ $data['company_image_1'] }}">
                    </div>

                    <div class="right_innercol">
                        <img src="{{ $data['company_image_2'] }}">
                    </div>

                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-6">
                <div class="ourcompany_right common_text">

                    <h3>{{ $data['company_title'] }}</h3>
                    <h2 class="common_heading">{{ $data['company_heading'] }}</h2>

                    {!! $data['company_text'] !!}

                </div>
            </div>

        </div>
    </div>
</div>

<div class="whocan_sec">
    <div class="container">
        <div class="whocan_row common_text">

            <h2 class="common_heading">{{ $data['whocan_heading'] }}</h2>

            {!! $data['whocan_text'] !!}

            <div class="whocan_img">
                <img src="{{ $data['whocan_image'] }}">
            </div>

        </div>
    </div>
</div>

<div class="trendingbrands_sec">
    <div class="container">

        <h2 class="common_heading">{{ $data['brands_title'] }}</h2>

        <div class="brands_slider" id="trendingbrands_slider">

            @foreach ([1, 2, 3, 4, 5, 6, 7] as $i)
                @if(!empty($data['brand_image_'.$i]))
                    <div class="slide">
                        <div class="brand_img">
                            <img src="{{ $data['brand_image_'.$i] }}">
                        </div>
                    </div>
                @endif
            @endforeach

        </div>

    </div>
</div>

<div class="ourmission_sec">
    <div class="container">
        <div class="row">

            <div class="col-12 col-md-6 col-lg-6">
                <div class="missionleft_col common_text">
                    <h2 class="common_heading">{{ $data['mission_heading'] }}</h2>
                    {!! $data['mission_text'] !!}
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-6">
                <div class="missionrightcol">

                    <div class="inner_leftcol">
                        <img src="{{ $data['mission_image_1'] }}">
                    </div>

                    <div class="inner_rightcol">
                        <img src="{{ $data['mission_image_2'] }}">
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection
