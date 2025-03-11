
@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<script>
    window.onload = function() {
        const userName = localStorage.getItem('user_name');
        if (userName) {
            // If user is logged in, display their name
            document.getElementById('userNameDashboard').textContent = userName;
        }
    }
</script>
<div class="home page-padding">
    <div class="container">
      <div class="row">
        <div class="col-12" class="image-page">
          <h1>{{ __('dashboard_welcome') }} <span id="userNameDashboard"></span></h1>
          <p>{{ __('dashboard_description') }}</p>
          <div>  <button id="updateStatsButton" 
            class="btn btn-primary" 
            style="cursor:pointer;">{{ __('dashboard_update') }}</button></div>
        </div>
      </div>
    </div>
  </div>
@endsection
