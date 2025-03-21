@extends('layout.layout')

@section('metaTitle', $metaSite['metaTitle'])
@section('metaDescription', $metaSite['metaDescription'])
@section('metaKeywords', $metaSite['metaKeywords'])

@section('content')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userName = localStorage.getItem('user_name');
        const accountId = localStorage.getItem('account_id');
        const server = localStorage.getItem('server') || 'eu';
        const updateButton = document.getElementById('updateStatsButton');
        const userNameElement = document.getElementById('userNameDashboard');
        let countdownInterval;
        
        if (userName && accountId) {
            // Create a link to the player's page
            const locale = "{{ app()->getLocale() }}";
            const playerLink = document.createElement('a');
            playerLink.href = `/${locale}/${server.toLowerCase()}/player/${userName}/${accountId}`;
            playerLink.textContent = userName;
            playerLink.classList.add('player-link');
            
            // Clear the span and append the link
            userNameElement.innerHTML = '';
            userNameElement.appendChild(playerLink);
        } else if (userName) {
            // If we have a username but no account ID, just show the name
            userNameElement.textContent = userName;
        }
        
        // Check if button should be disabled due to rate limiting
        function checkRateLimit() {
            const lastUpdateTime = localStorage.getItem('lastStatsUpdate');
            
            if (lastUpdateTime) {
                const now = new Date().getTime();
                const timeSinceLastUpdate = now - parseInt(lastUpdateTime);
                const waitTime = 5 * 60 * 1000; // 5 minutes in milliseconds
                
                if (timeSinceLastUpdate < waitTime) {
                    // Calculate remaining time
                    const remainingTime = Math.ceil((waitTime - timeSinceLastUpdate) / 1000);
                    
                    // Disable button and show countdown
                    updateButton.disabled = true;
                    updateCountdown(remainingTime);
                    
                    return true; // Rate limited
                }
            }
            
            return false; // Not rate limited
        }
        
        // Update countdown on button
        function updateCountdown(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            
            updateButton.innerHTML = `{{ __("dashboard_update") }} (${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds})`;
            
            if (seconds > 0) {
                // Schedule next countdown update
                clearTimeout(countdownInterval);
                countdownInterval = setTimeout(() => updateCountdown(seconds - 1), 1000);
            } else {
                // Reset when countdown is done
                updateButton.disabled = false;
                updateButton.innerHTML = '{{ __("dashboard_update") }}';
            }
        }
        
        // Check rate limit when page loads
        checkRateLimit();
        
        // Add event listener for update button
        updateButton.addEventListener('click', function() {
            // If already rate limited, don't proceed
            if (checkRateLimit()) {
                return;
            }
            
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
            this.innerHTML = 'Updating...';
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
                // Save the update timestamp in localStorage
                localStorage.setItem('lastStatsUpdate', new Date().getTime());
                
                // Start the cooldown
                checkRateLimit();
                
                // Show success message
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                updateButton.innerHTML = '{{ __("dashboard_update") }}';
                updateButton.disabled = false;
                alert('Error updating player stats: ' + error.message);
            });
        });
    });
</script>
<div class="home page-padding">
    <div class="container">
      <div class="row">
        <div class="col-12" class="image-page">
          <h1>{{ __('dashboard_welcome') }} <span id="userNameDashboard" style="text-decoration: underline #0B5ED7; color: #004bbc;"></span></h1>
          <p>{{ __('dashboard_description') }}</p>
          <div>  <button id="updateStatsButton" 
            class="btn btn-primary" 
            style="cursor:pointer;">{{ __('dashboard_update') }}</button></div>
        </div>
      </div>
    </div>
  </div>
@endsection
