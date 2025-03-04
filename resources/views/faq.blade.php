@extends('layout.layout')

@section('metaTitle', __('_page_faq_title'))
@section('metaDescription', __('_page_faq_desc'))
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="home page-padding">
    <div class="container">
        <div class="row mb-40">
            <div class="col-12">
        <div>
                    {!! __('_page_faq') !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
