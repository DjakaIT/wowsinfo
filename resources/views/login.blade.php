@extends('layout.layout')

@section('metaTitle', __('seo_login_title'))
@section('metaDescription', __('_login_description'))
@section('metaKeywords', __('seo_login_keywords'))


@section('content')
<div class="about">
  <h1>{{ __('_login_title') }}</h1>
  <h2>{{ __('_login_description') }}</h2>
  <ul>
    <li class="pointer">
      <a href="https://api.worldoftanks.eu/wot/auth/login/?application_id=746553739e1c6e051e8d4fa24671ac01&redirect_uri=http://wows.wn8.info/verification">
        {{ __('_login_eu_server') }}</a></li>
    <li class="pointer">{{ __('_login_asia_server') }}</li>
    <li class="pointer">{{ __('_login_na_server') }}</li>
  </ul>
</div>
@endsection
