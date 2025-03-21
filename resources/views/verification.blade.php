@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<div class="about">
  <h1>Verification</h1>
  <script>
        // Check if the Blade variables have been passed
        if ('{{ $nickname }}' && '{{ $access_token }}') {

            //get server from url
            const urlParams = new URLSearchParams(window.location.search);
            const server = urlParams.get('server') || 'eu';

            // Store the data in localStorage
            localStorage.setItem('user_name', '{{ $nickname }}');
            localStorage.setItem('account_id', '{{ $account_id }}');
            localStorage.setItem('access_token', '{{ $access_token }}');
            localStorage.setItem('expires_at', '{{ $expires_at }}');
            localStorage.setItem('server', server);
            
            window.location.href = '{{ route('dashboard') }}';
        }

        // Redirect to home page after storing the data
        // ;
    </script>
</div>
@endsection
