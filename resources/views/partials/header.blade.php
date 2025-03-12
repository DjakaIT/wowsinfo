<script>
    // Check login state immediately and on DOM content loaded
    function updateLoginState() {
        const userName = localStorage.getItem('user_name');
        const loginLink = document.getElementById('loginLink');
        const loggedSection = document.getElementById('loggedSection');
        const userNameElement = document.getElementById('userName');
        
        if (userName && loginLink && loggedSection && userNameElement) {
            // If user is logged in, display their name
            userNameElement.textContent = userName;
            loginLink.style.display = 'none'; // Hide login link
            loggedSection.style.display = 'block'; // Show logout link
            console.log('User is logged in as:', userName);
        } else {
            // If user is not logged in or elements not found yet
            if (loginLink) loginLink.style.display = 'block';
            if (loggedSection) loggedSection.style.display = 'none';
        }
    }

    // Run immediately if elements exist
    updateLoginState();
    
    // Also run when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', updateLoginState);
    
    // Keep original onload but call our function first
    window.onload = function() {
        // Update login state first
        updateLoginState();
        
        // Rest of your existing code
        const searchInput = document.getElementById("playerSearch");
        const resultsContainer = document.getElementById("results");
        const wargamingId = "746553739e1c6e051e8d4fa24671ac01"; // Fetch from Laravel config
        const serverDropdown = document.querySelector('button.dropdown-toggle');
        let server = serverDropdown.textContent.toLowerCase(); // Get server from dropdown text

        const searchTypeDropdown = document.querySelectorAll('button.dropdown-toggle')[1];
        let searchType = searchTypeDropdown.textContent.trim().toLowerCase(); // Get search type from dropdown text
        
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
                const newServer = this.textContent.toLowerCase();
                // Don't just update the text, navigate to the server change URL
                window.location.href = `/server/${newServer}`;
            });
        });

        let timeout = null;


        const searchTypeOptions = document.querySelectorAll('.dropdown-menu:nth-of-type(2) .dropdown-item');
        searchTypeOptions.forEach(option => {
        option.addEventListener('click', function() {
            searchType = this.textContent.trim().toLowerCase();
            searchTypeDropdown.textContent = this.textContent;

            const optionText = this.textContent.trim().toLowerCase();
            
            // Update placeholder based on search type
            if (optionText.includes('{{ strtolower(__("nav_clan")) }}')) {
            searchInput.placeholder = "{{ __('nav_clan') }} | TAG";
        } else {
            searchInput.placeholder = "{{ __('nav_player') }}";
        }
    });
    });



        searchInput.addEventListener("input", function() {
            const query = searchInput.value.trim();

            if (query.length < 3) {
                resultsContainer.innerHTML = "";
                resultsContainer.style.display = "none";
                return;
            }

            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // Choose API endpoint based on search type
                let endpoint;
                if (searchType.includes('clan')) {
                    endpoint = "wows/clans/list/";
                } else {
                    endpoint = "wows/account/list/";
                }

                // Map server to correct domain
                const apiDomain = server === 'na' ? 'com' : (server === 'asia' ? 'asia' : 'eu');

                fetch(`https://api.worldofwarships.${apiDomain}/${endpoint}?application_id=${wargamingId}&search=${query}`)
                    .then(response => response.json())
                    .then(response => {
                        resultsContainer.innerHTML = "";
                        
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(item => {
                                const listItem = document.createElement("li");
                                listItem.classList.add("dropdown-item");
                                listItem.style.cssText = "cursor:pointer";
                                
                                if (searchType.includes('clan')) {
                                    // For clan results
                                    listItem.textContent = `[${item.tag}] ${item.name}`;
                                    listItem.dataset.clanId = item.clan_id;
                                    listItem.addEventListener("click", function() {
                                        // Get current locale and server
                                        const locale = "{{ app()->getLocale() }}";
                                        const serverVal = server || "eu";
                                        // Redirect to clan page with locale and server
                                        window.location.href = `/${locale}/${serverVal}/clan/${item.tag}/${item.clan_id}`;
                                    });
                                } else {
                                    // For player results (existing code)
                                    listItem.textContent = `${item.nickname}`;
                                    listItem.dataset.accountId = item.account_id;
                                    listItem.addEventListener("click", function() {
                                        // Get current locale and server
                                        const locale = "{{ app()->getLocale() }}";
                                        const serverVal = server || "eu";
                                        // Redirect to player page with locale and server
                                        window.location.href = `/${locale}/${serverVal}/player/${item.nickname}/${item.account_id}`;
                                    });
                                }
                                
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

        // Also update the search button click handler
        document.getElementById('button-addon2').addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query.length >= 3) {
                // Choose API endpoint based on search type
                let endpoint;
                if (searchType.includes('clan')) {
                    endpoint = "wows/clans/list/";
                } else {
                    endpoint = "wows/account/list/";
                }
                
                // Map server to correct domain
                const apiDomain = server === 'na' ? 'com' : (server === 'asia' ? 'asia' : 'eu');
                
                fetch(`https://api.worldofwarships.${apiDomain}/${endpoint}?application_id=${wargamingId}&search=${query}`)
                    .then(response => response.json())
                    .then(response => {
                        if (response.data && response.data.length > 0) {
                            if (searchType.includes('clan')) {
                                // Get current locale and server
                                const locale = "{{ app()->getLocale() }}";
                                const serverVal = server || "eu";
                                // Redirect to the first matching clan
                                window.location.href = `/${locale}/${serverVal}/clan/${response.data[0].tag}/${response.data[0].clan_id}`;
                            } else {
                                // Get current locale and server
                                const locale = "{{ app()->getLocale() }}";
                                const serverVal = server || "eu";
                                // Redirect to the first matching player
                                window.location.href = `/${locale}/${serverVal}/player/${response.data[0].nickname}/${response.data[0].account_id}`;
                            }
                        }
                    })
                    .catch(error => console.error("Error fetching data:", error));
            }
        });
    }
    
    function logout() {
    // Get and validate the server BEFORE clearing localStorage
    const rawServer = localStorage.getItem('server') || 'eu';
    const validServer = ['eu', 'na', 'asia'].includes(rawServer.toLowerCase()) ? rawServer.toLowerCase() : 'eu';
    
    // Store the validated server in a cookie that won't be cleared with localStorage
    document.cookie = `last_valid_server=${validServer}; path=/; max-age=60`;
    
    // Clear localStorage items
    localStorage.removeItem('access_token');
    localStorage.removeItem('user_name');
    localStorage.removeItem('account_id');
    localStorage.removeItem('expires_at');
    localStorage.removeItem('server');
    
    // Get the current locale
    const locale = "{{ app()->getLocale() }}";
    
    // Call the Wargaming API
    fetch(`https://api.worldofwarships.${validServer === 'na' ? 'com' : (validServer === 'asia' ? 'asia' : 'eu')}/wot/auth/logout/?application_id=746553739e1c6e051e8d4fa24671ac01&access_token=`)
        .then(response => response.json())
        .catch(error => console.error('Error during logout:', error))
        .finally(() => {
            // Hard redirect to the home route with explicit server parameter
            window.location.href = `/${locale}/${validServer}`;
        });
}

    function changeLanguage() {
    var locale = document.getElementById('language').value;
    
    // Get current URL to check if we're on a locale-aware page
    const currentPath = window.location.pathname;
    const match = currentPath.match(/^\/([a-z]{2})\/(eu|na|asia)\/(wiki|player|clan)(.*)$/);
    
    if (match) {
        // If we're on a locale-aware page, just change the locale part
        const currentLocale = match[1];
        const server = match[2];
        const type = match[3];
        const rest = match[4];
        window.location.href = `/${locale}/${server}/${type}${rest}`;
    } else {
        // Otherwise use the old route
        window.location.href = '/locale/' + locale;
    }
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
				<a class="nav-link" href="{{ route('wiki.home', [
                'locale' => app()->getLocale(),
                'server' => strtolower(session('server', 'eu'))
            ]) }}">{{ __('nav_wiki') }}</a>
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
