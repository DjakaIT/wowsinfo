
@extends('layout.layout')

@section('metaTitle', __('seo_wiki_type_title'))
@section('metaDescription', __('seo_wiki_type_content'))
@section('metaKeywords', __('seo_wiki_type_keywords'))

@section('content')
<div class="wiki">
    <div class="container">
        <div class="row mb-50">
            <div class="col-12">
                <h1 class="page-heading">
                    <span>{{ __('wiki_' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type)))) }}</span>
                </h1>
                <ul class="wiki-breadcrumb">
                  <li><a href="/wiki/" class="router-link-active"> Wiki </a><span> / &nbsp;</span></li>
                  <li class="capitalize"><span>{{ __('wiki_' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type)))) }}</span></li>
                </ul>
            </div>
            @if($type !== 'Submarine')
                <div class="col-12">
                    <p class="wiki-text-info">
                       {{ $description }} 
                    </p>
                </div>
            @endif
        </div>

        @foreach($nations as $key => $nation)
            <div class="row wiki-group-holder mb-50">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 wiki-section-title">
                            <img src="{{ $nationImages[$key] ?? '' }}" alt="Nation Image">
                            <h2>{{ __('wiki_nation_' . $key) }}</h2>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($nation as $vehicle)
                            <div class="col-2 wiki-type-item">
                                <a href="{{ route('wiki.vehicle', [
                                    'nation' => $key,
                                    'type' => $type,
                                    'ship' => $vehicle['name'],
                                    'shipId' => $vehicle['id']
                                ]) }}">
                                    <img src="{{ $vehicle['image'] }}" alt="{{ $vehicle['name'] }}">
                                    <span>{{ $vehicle['name'] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
