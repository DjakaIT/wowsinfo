@extends('layout.layout')

@section('metaTitle', __('seo_wiki_title'))
@section('metaDescription', __('seo_wiki_content'))
@section('metaKeywords', __('seo_wiki_keywords'))

@section('content')
	<div class="wiki">
		<h1 class="page-heading mb-50">{{ __('wiki_home_title') }}</h1>
		<div class="mb-50">
			<p class="wiki-text-info">{{__('_wiki_home_description') }}</p>
		</div>
		<h2 class="page-subheading">{{__('nations') }}</h2>
		<div class="wiki-nation-group mb-50">
			@foreach ($nations as $nation)
				<div class="wiki-nation-item">
					<a href="{{ route('wiki.nation', [
					    'locale' => app()->getLocale(),
					    'server' => strtolower(session('server', 'eu')),
					    'nation' => $nation
					]) }}">
						<img src="{{ $nationImages[$nation] }}" />
					</a>
				</div>
			@endforeach
		</div>
		<h2 class="page-subheading">{{__('warship_types') }}</h2>
		<div class="wiki-type-group-home">
			@foreach ($types as $type)
				<div class="wiki-type-item">
					<a href="{{ route('wiki.type', [
					    'locale' => app()->getLocale(), 
					    'server' => strtolower(session('server', 'eu')),
					    'type' => strtolower($type)
					]) }}">
						<img src="{{ asset('images/' . $type . '.png') }}" />
						@if ($type === 'Aircarrier')
							<span>{{ __('wiki_AirCarrier') }}</span>
						@else
							<span>{{ __('wiki_' . $type) }}</span>
						@endif
					</a>
				</div>
			@endforeach
		</div>
	</div>
@endsection