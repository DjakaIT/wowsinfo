@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="wiki">
    <div class="container">
      <div class="row mb-50">
        <div class="col-12">
          <h1 class="page-heading">{{ $nation }}</h1>
          <ul class="wiki-breadcrumb">
						<li><a href="/wiki/" class="router-link-active"> Wiki </a><span> / &nbsp;</span></li>
						<li><span>{{ $nation }}</span></li>
					</ul>
        </div>
        <div class="col-12">
          <p class="wiki-text-info">
            {{ $description }}
          </p>
				</div>
      </div>
      <div class="row wiki-group-holder mb-50">
				@foreach ($types as $key => $type)
						<div class="col-12">
								<div class="row">
										<div class="col-12 wiki-section-title">
											<h2>{{ 'wiki_' . $key }}</h2>
										</div>
								</div>
								<div class="row">
										@foreach ($type as $vehicle)
												<div class="col-2 wiki-type-item">
														<a href="{{ route('wiki.vehicle', [
																'nation' => $nation,
																'type' => $key,
																'ship' => $vehicle['name']
														]) }}">
																<img src="{{ $vehicle['image'] }}" alt="{{ $vehicle['name'] }}">
																<span>{{ $vehicle['name'] }}</span>
														</a>
												</div>
										@endforeach
								</div>
						</div>
				@endforeach
		</div>
    </div>
  </div>
@endsection