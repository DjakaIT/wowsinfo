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

        const updateButton = document.getElementById('updateStatsButton');
            if (updateButton) {
                updateButton.disabled = false;
            }
    }

    function updatePlayerStats() {
        const userName = localStorage.getItem('user_name');
        if (!userName) {
            showStatusMessage('Please log in to update your stats', 'error');
            return;
        }
        
        // Show loading state
        const button = document.getElementById('updateStatsButton');
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = 'Updating...';
        showStatusMessage('Updating your stats, please wait...', 'info');
        
        // Send request to update stats
        fetch('{{ route('update.player.stats') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ username: userName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatusMessage(data.message, 'success');
            } else {
                showStatusMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showStatusMessage('An error occurred while updating stats', 'error');
            console.error('Error:', error);
        })
        .finally(() => {
            // Reset button state
            button.disabled = false;
            button.textContent = originalText;
        });
    }
    
    function showStatusMessage(message, type) {
        const statusElement = document.getElementById('updateStatus');
        statusElement.textContent = message;
        statusElement.className = 'update-status ' + type;
        statusElement.style.display = 'block';
        
        // Hide message after 5 seconds
        setTimeout(() => {
            statusElement.style.display = 'none';
        }, 5000);
    }
</script>

<style>
    .update-status {
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
        display: none;
    }
    .update-status.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .update-status.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .update-status.info {
        background-color: #cce5ff;
        color: #004085;
        border: 1px solid #b8daff;
    }
</style>

<div class="home page-padding">
    <div class="container">
      <div class="row">
        <div class="col-12" class="image-page">
          <h1>{{ __('dashboard_welcome') }} <span id="userNameDashboard"></span></h1>
          <p>{{ __('dashboard_description') }}</p>
          <div>
            <button id="updateStatsButton" 
              onclick="updatePlayerStats()" 
              class="btn btn-primary" 
              style="cursor:pointer;">{{ __('dashboard_update') }}</button>
            <!-- Add the missing status element here -->
            <div id="updateStatus" class="update-status"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection