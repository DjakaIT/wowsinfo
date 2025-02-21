@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="about">
  <h1>Login</h1>
  <h2>Choose your server</h2>
  <ul>
    <li class="pointer">
      <a href="https://api.worldoftanks.eu/wot/auth/login/?application_id=746553739e1c6e051e8d4fa24671ac01&redirect_uri=http://wows.wn8.info/verification">
        Europe server</a></li>
    <li class="pointer">Asia server</li>
    <li class="pointer">North America server</li>
  </ul>
</div>
@endsection
