@extends('layout.layout')

@section('metaTitle', __('_page_imprint_title'))
@section('metaDescription', __('_page_imprint_desc'))
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="home page-padding">
    <div class="container">
        <div class="row mb-40">
            <div class="col-12">
        <div>
            <h1>{!! __('_page_imprint_title') !!}</h1>
			<p>{!! __('_page_imprint_desc') !!}</p>
          {!! __('_page_imprint') !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection