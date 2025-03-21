@extends('layout.layout')

@section('metaTitle', __('seo_wiki_nation_title'))
@section('metaDescription', __('seo_wiki_nation_content'))
@section('metaKeywords', __('seo_wiki_nation_keywords'))

@section('content')
<div class="wiki">
    <div class="container">
      <div class="row mb-50">
        <div class="col-12">
          <h1 class="page-heading">{{ ucfirst($nation) }}</h1>
          <ul class="wiki-breadcrumb">
						<li><a href="{{ route('wiki.home', [
    'locale' => app()->getLocale(),
    'server' => strtolower(session('server', 'eu'))
]) }}" class="router-link-active"> Wiki </a><span> / &nbsp;</span></li>
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
										<h2>
											@if(strtolower($key) === 'aircarrier' || strtolower($key) === 'air carrier')
												{{ __('wiki_AirCarrier') }}
											@else
												{{ __('wiki_' . ucfirst(strtolower(str_replace(' ', '', $key)))) }}
											@endif
										</h2>
									</div>
								</div>
								<div class="row">
										@foreach ($type as $vehicle)
												<div class="col-2 wiki-type-item">
													<a href="{{ route('wiki.vehicle', [
														'locale' => app()->getLocale(),
														'server' => strtolower(session('server', 'eu')),
														'nation' => $nation,
														'type' => strtolower(str_replace(' ', '', $key)),
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
				@endforeach
		</div>
    </div>
  </div>
@endsection
