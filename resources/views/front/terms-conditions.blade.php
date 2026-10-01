@extends('front.layout.master')

@section('style')
<style>
.terms_accordion .accordion-item {
    border: none;
    border-bottom: 1px solid #e5e5e5;
    border-radius: 0;
}
.terms_accordion .accordion-item:first-child {
    border-top: 1px solid #e5e5e5;
}
.terms_accordion .accordion-header .accordion-button {
    padding: 18px 8px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    color: var(--bs-secondary);
    background: transparent;
    box-shadow: none;
    border-radius: 0;
}
.terms_accordion .accordion-header .accordion-button:not(.collapsed) {
    color: var(--bs-primary);
    background: transparent;
    box-shadow: none;
}
.terms_accordion .accordion-header .accordion-button::after {
    background-image: none;
    content: "\25B8";
    font-size: 16px;
    line-height: 1;
    transition: transform 0.2s ease-in-out;
}
.terms_accordion .accordion-header .accordion-button:not(.collapsed)::after {
    transform: rotate(90deg);
}
.terms_accordion .accordion-body {
    padding: 0 8px 22px;
    color: #666666;
    line-height: 24px;
}
.terms_accordion .accordion-body p {
    margin-bottom: 12px;
}
</style>
@endsection

@section('content')

<!-- breadcrumb -->
<div class="breadcrumb_sec">
    <div class="container">
        <div class="breadcrumb_row">
            <ul>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><span>/</span></li>
                <li class="active"><a href="javascript:void(0);">{{ $pageTitle }}</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="static_page_sec">
    <div class="container">
        <h1 class="common_heading mb-4">{{ $pageTitle }}</h1>

        @if(count($sections))
            <div class="accordion terms_accordion" id="termsAccordion">
                @foreach($sections as $index => $section)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="termsHeading{{ $index }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#termsCollapse{{ $index }}" aria-expanded="false" aria-controls="termsCollapse{{ $index }}">
                                {{ $section['heading'] ?? '' }}
                            </button>
                        </h2>
                        <div id="termsCollapse{{ $index }}" class="accordion-collapse collapse" aria-labelledby="termsHeading{{ $index }}"
                            data-bs-parent="#termsAccordion">
                            <div class="accordion-body">
                                {!! $section['content'] ?? '' !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p>Content for this page hasn't been added yet.</p>
        @endif
    </div>
</div>

@endsection
