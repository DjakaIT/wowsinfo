@extends('layout.layout')

@section('metaTitle', __('_page_privacypolicy_title'))
@section('metaDescription', __('_page_privacypolicy_desc'))
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="home page-padding">
    <div class="container">
        <div class="row mb-40">
            <div class="col-12">
        <div>
          {!! __('_page_privacypolicy') !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection