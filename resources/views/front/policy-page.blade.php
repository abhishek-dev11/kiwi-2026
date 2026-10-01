@extends('front.layout.master')

@section('style')
<style>
.policy_content h1, .policy_content h2, .policy_content h3, .policy_content h4 {
    color: var(--bs-secondary);
    font-weight: 600;
    margin-top: 24px;
    margin-bottom: 12px;
}
.policy_content h1:first-child, .policy_content h2:first-child, .policy_content h3:first-child {
    margin-top: 0;
}
.policy_content p {
    color: #666666;
    line-height: 24px;
    margin-bottom: 16px;
}
.policy_content ul, .policy_content ol {
    color: #666666;
    line-height: 24px;
    margin-bottom: 16px;
    padding-left: 20px;
}
.policy_content a {
    color: var(--bs-primary);
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

        @if($pageContent)
            <div class="policy_content">
                {!! $pageContent !!}
            </div>
        @else
            <p>Content for this page hasn't been added yet.</p>
        @endif
    </div>
</div>

@endsection
