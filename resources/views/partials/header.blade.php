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
        axios.get(`https://api.worldoftanks.eu/wot/auth/logout/?application_id=746553739e1c6e051e8d4fa24671ac01&access_token=${localStorage.getItem('access_token')}`)
        .then(response => {
            // If the logout is successful, clear localStorage or cookies
            if (response.data.success) {
                // Clear localStorage (or cookies if used)
                localStorage.removeItem('access_token');
                localStorage.removeItem('user_name');
                localStorage.removeItem('account_id');
                localStorage.removeItem('expires_at');

                // Reload the page to update the login state
                window.location.reload(); // Uncommented to ensure proper state update
            } else {
                alert('Failed to log out from Wargaming. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error logging out from Wargaming:', error);
        });
    }


    const languageOptions = document.querySelectorAll('#languageDropdown + .dropdown-menu .dropdown-item');
const languageDropdown = document.getElementById('languageDropdown');

// Get saved language or use default
const savedLang = localStorage.getItem('selectedLanguage') || 'EN';
languageDropdown.textContent = savedLang;

languageOptions.forEach(option => {
    option.addEventListener('click', function(e) {
        e.preventDefault();
        const lang = this.getAttribute('data-lang').toUpperCase();
        languageDropdown.textContent = lang;
        
        // Save selection to localStorage
        localStorage.setItem('selectedLanguage', lang);
        
        // Here you would typically trigger a language change in your application
        // For example: window.location.href = `?lang=${lang.toLowerCase()}`;
    });
});

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
				<a class="nav-link" href="/">Home</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="/wiki">Wiki</a>
			</li>
			<li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    EN
                </a>
                <ul class="dropdown-menu" aria-labelledby="languageDropdown" style="max-height: 400px; overflow-y: auto;">
                    <li><a class="dropdown-item" href="#" data-lang="en">English</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="bg">Български</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="de">Deutsch</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="cs">Česky</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="es">Español</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="fi">Suomi</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="fr">Français</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="hr">Hrvatski</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="ko">한국의</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="hu">Magyar</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="it">Italiano</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="nl">Nederlands</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="pl">Polski</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="pt">Português</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="ru">Русский</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="ro">Românesc</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="sk">Slovenčina</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="sr">Srpski</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="tr">Türkçe</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="uk">Yкраїнський</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="ja">Japanese</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="ar">Arabian</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="tl">Philipines</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="gr">Greece</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="se">Sweeden</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="vn">Viet Nam</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="af">Afrikaans</a></li>
                </ul>
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
					<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Player</button>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="#">Player</a></li>
						<li><a class="dropdown-item" href="#">Clan</a></li>
					</ul>
					<input id="playerSearch" type="text" class="form-control" aria-label="Text input with dropdown button">
					<button class="btn btn-outline-secondary" type="button" id="button-addon2">Search</button>
					<ul id="results" class="dropdown-menu show w-100 player-search-dropdown" style="display: none;"></ul>
				</div>
			</li>

			<li id="loginLink" class="nav-item">
				<a class="nav-link" href="/login">Login</a>
			</li>

			<li id="loggedSection" class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
					<em id="userName"></em>
				</a>
				<ul class="dropdown-menu" aria-labelledby="userDropdown">
					<li><a class="dropdown-item" href="/dashboard">Dashboard</a></li>
					<li><a class="dropdown-item" href="#" onclick="logout()">Logout</a></li>
				</ul>
			</li>
		</ul>
	</div>
</nav>