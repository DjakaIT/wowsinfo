
@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<script>
        document.addEventListener('DOMContentLoaded', function() {
        const userName = localStorage.getItem('user_name');
        if (userName) {
            // If user is logged in, display their name
            document.getElementById('userNameDashboard').textContent = userName;
        }
        
        // Add event listener for update button
        document.getElementById('updateStatsButton').addEventListener('click', function() {
            // Get current URL and extract username if on a player page
            const currentPath = window.location.pathname;
            // Check if we're on a player page
            const match = currentPath.match(/\/[a-z]{2}\/(eu|na|asia)\/player\/([^\/]+)\/(\d+)/);
            
            let playerName = '';
            
            if (match) {
                playerName = match[2];
            } else {
                // If not on a player page, use the logged-in user's name
                playerName = localStorage.getItem('user_name');
            }
            
            if (!playerName) {
                console.log('No player name found');
                return;
            }
            
            // Show loading indicator
            this.innerHTML = '{{ __("dashboard_updating") }}...';
            this.disabled = true;
            
            fetch('/api/player/lookup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    username: playerName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Account found:', data.account_id);
                    // Now update the player's stats
                    return fetch('/api/player/lookup', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            username: playerName,
                            account_id: data.account_id
                        })
                    });
                } else {
                    throw new Error(data.message || 'Player not found');
                }
            })
            .then(response => response.json())
            .then(data => {
                this.innerHTML = '{{ __("dashboard_update") }}';
                this.disabled = false;
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                this.innerHTML = '{{ __("dashboard_update") }}';
                this.disabled = false;
                alert('Error updating player stats: ' + error.message);
            });
        });
    });
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
