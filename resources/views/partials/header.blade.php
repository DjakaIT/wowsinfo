<script>
    // Check if user is logged in by checking localStorage
    window.onload = function() {
        const userName = localStorage.getItem('user_name');

        if (userName) {
            // If user is logged in, display their name
            document.getElementById('userName').textContent = userName;
            document.getElementById('loginLink').style.display = 'none'; // Hide login link
            document.getElementById('loggedSection').style.display = 'block'; // Show logout link
        } else {
            // If user is not logged in, show the login link
            document.getElementById('loginLink').style.display = 'block';
            document.getElementById('loggedSection').style.display = 'none';
        }

        const searchInput = document.getElementById("playerSearch");
        const resultsContainer = document.getElementById("results");
        const wargamingId = "746553739e1c6e051e8d4fa24671ac01"; // Fetch from Laravel config
        const serverDropdown = document.querySelector('button.dropdown-toggle');
        let server = serverDropdown.textContent.toLowerCase(); // Get server from dropdown text
        
        // Close dropdown when clicking elsewhere - FIXED VERSION
        document.addEventListener('click', function(event) {
            const searchContainer = document.querySelector('.input-group');
            // Check if the click was outside the search container and the dropdown is visible
            if (!searchContainer.contains(event.target) && resultsContainer.style.display === "block") {
                resultsContainer.style.display = "none";
            }
        });

        // Make search input click stop propagation to prevent immediate closing
        searchInput.addEventListener('click', function(event) {
            event.stopPropagation();
        });
        
        // Stop propagation for result items clicks to handle them properly
        resultsContainer.addEventListener('click', function(event) {
            event.stopPropagation();
        });

        // Update server when dropdown selection changes
        const serverOptions = document.querySelectorAll('.dropdown-menu:first-of-type .dropdown-item');
        serverOptions.forEach(option => {
            option.addEventListener('click', function() {
                server = this.textContent.toLowerCase();
                serverDropdown.textContent = this.textContent;
            });
        });

        let timeout = null;

        searchInput.addEventListener("input", function() {
            const query = searchInput.value.trim();

            if (query.length < 3) {
                resultsContainer.innerHTML = "";
                resultsContainer.style.display = "none";
                return;
            }

            clearTimeout(timeout);
            timeout = setTimeout(() => {
                fetch(`https://api.worldofwarships.${server}/wows/account/list/?application_id=${wargamingId}&search=${query}`)
                    .then(response => response.json())
                    .then(response => {
                        resultsContainer.innerHTML = "";
                        
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(player => {
                                const listItem = document.createElement("li");
                                listItem.classList.add("dropdown-item");
                                listItem.textContent = `${player.nickname}`;
                                // Add data attribute to store the account_id
                                listItem.dataset.accountId = player.account_id;
								listItem.style.cssText = "cursor:pointer";
                                listItem.addEventListener("click", function() {
                                    // Redirect to player page with name and ID
                                    window.location.href = `/player/${player.nickname}/${player.account_id}`;
                                });
                                resultsContainer.appendChild(listItem);
                            });
                            resultsContainer.style.display = "block";
                        } else {
                            resultsContainer.innerHTML = "<li class='dropdown-item'>No results found</li>";
                            resultsContainer.style.display = "block";
                        }
                    })
                    .catch(error => console.error("Error fetching data:", error));
            }, 500); // Debounce API calls
        });

        // Also trigger search on search button click
        document.getElementById('button-addon2').addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query.length >= 3) {
                // Perform the same search as in the input event
                fetch(`https://api.worldofwarships.${server}/wows/account/list/?application_id=${wargamingId}&search=${query}`)
                    .then(response => response.json())
                    .then(response => {
                        if (response.data && response.data.length > 0) {
                            // Redirect to the first match
                            window.location.href = `/player/${response.data[0].nickname}/${response.data[0].account_id}`;
                        }
                    })
                    .catch(error => console.error("Error fetching data:", error));
            }
        });
    }
    
    function logout() {
    // Get the server the user logged in from
    const server = localStorage.getItem('server') || 'eu';
    
    // Clear localStorage first (so logout works even if API call fails)
    localStorage.removeItem('access_token');
    localStorage.removeItem('user_name');
    localStorage.removeItem('account_id');
    localStorage.removeItem('expires_at');
    localStorage.removeItem('server');
    
    // Determine the correct API domain
    const apiDomain = server === 'na' ? 'com' : (server === 'asia' ? 'asia' : 'eu');
    const accessToken = localStorage.getItem('access_token') || '';
    
    // Call the Wargaming API with the correct domain
    fetch(`https://api.worldoftanks.${apiDomain}/wot/auth/logout/?application_id=746553739e1c6e051e8d4fa24671ac01&access_token=${accessToken}`)
        .then(response => response.json())
        .then(data => {
            console.log('Logout successful');
            // Redirect to home page to refresh state
            window.location.href = '/';
        })
        .catch(error => {
            console.error('Error during logout:', error);
            // Still redirect even if API call fails
            window.location.href = '/';
        });
}

    function changeLanguage() {
    var locale = document.getElementById('language').value;
    window.location.href = '/locale/' + locale;
}

</script>
<nav class="navbar navbar-expand-lg navbar-dark shadow4">
	<a class="navbar-brand" href="/">
		<img src="{{ asset('images/logo-white.png') }}" alt="logo">
	</a>
	<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav-collapse" aria-controls="nav-collapse" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="nav-collapse">
		<ul class="navbar-nav me-auto mb-2 mb-lg-0">
			<li class="nav-item">
				<a class="nav-link" href="/">{{ __('nav_home') }}</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="/wiki">{{ __('nav_wiki') }}</a>
			</li>
			<li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ App::getLocale() }}
                </a>
                <div class="dropdown-menu" aria-labelledby="languageDropdown" style="max-height: 400px; overflow-y: auto;">
                    <form id="localeForm" class="px-3 py-2">
                        <select name="locale" id="language" class="form-select" onchange="changeLanguage()">
                            @foreach(config('app.available_locales') as $name => $locale)
                                <option value="{{ $locale }}" {{ app()->getLocale() == $locale ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </li>
		</ul>

		<!-- Right-aligned nav items -->
			
		<ul class="navbar-nav">
			<li class="nav-item relative">
				<div class="input-group">
					<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">EU</button>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="#">EU</a></li>
						<li><a class="dropdown-item" href="#">NA</a></li>
						<li><a class="dropdown-item" href="#">ASIA</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item disabled" href="#">RU</a></li>
					</ul>
					<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ __( 'nav_player') }}</button>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="#">{{ __('nav_player') }}</a></li>
						<li><a class="dropdown-item" href="#">{{ __('nav_clan') }}</a></li>
					</ul>
					<input id="playerSearch" type="text" class="form-control" aria-label="Text input with dropdown button">
					<button class="btn btn-outline-secondary" type="button" id="button-addon2">{{ __('nav_search') }}</button>
					<ul id="results" class="dropdown-menu show w-100 player-search-dropdown" style="display: none;"></ul>
				</div>
			</li>

			<li id="loginLink" class="nav-item">
				<a class="nav-link" href="/login">{{ __('nav_login') }}</a>
			</li>

			<li id="loggedSection" class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
					<em id="userName"></em>
				</a>
				<ul class="dropdown-menu" aria-labelledby="userDropdown">
					<li><a class="dropdown-item" href="/dashboard">{{ __('nav_dashboard') }}</a></li>
					<li><a class="dropdown-item" href="#" onclick="logout()">{{ __('nav_logout') }}</a></li>
				</ul>
			</li>
		</ul>
	</div>
</nav>