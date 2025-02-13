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
        const server = "eu"; // Adjust as needed

        let timeout = null;

        searchInput.addEventListener("input", function () {
            const query = searchInput.value.trim();

            if (query.length < 3) {
                resultsContainer.innerHTML = "";
                return;
            }

            clearTimeout(timeout);
            timeout = setTimeout(() => {
                fetch(`https://api.worldofwarships.${server}/wows/account/list/?application_id=${wargamingId}&search=${query}`)
								.then(response => response.json())
                .then(response => {
									resultsContainer.innerHTML = "";
									console.log(response)
                    if (response.data) {
											response.data.forEach(player => {
												const listItem = document.createElement("li");
												listItem.classList.add("dropdown-item");
												listItem.textContent = `${player.nickname} (ID: ${player.account_id})`;
												listItem.addEventListener("click", function () {
														searchInput.value = player.nickname;
														resultsContainer.style.display = "none";
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
							// window.location.reload();
					} else {
							alert('Failed to log out from Wargaming. Please try again.');
					}
			})
			.catch(error => {
					console.error('Error logging out from Wargaming:', error);
			});
    }

		// Search player api

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
			{{-- <li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
					nav_lang
				</a>
				<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
					<li><a class="dropdown-item" href="#" >KEY1 VALUE1</a></li>
					<li><a class="dropdown-item" href="#" >KEY2 VALUE2</a></li>
				</ul>
			</li>--}}
		</ul>

		<!-- Right-aligned nav items -->
			
		<ul class="navbar-nav">
			<li class="nav-item relative">
				<div class="input-group">
					<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">EU</button>
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="#">EU</a></li>
						<li><a class="dropdown-item" href="#">NA</a></li>
						<li><a class="dropdown-item" href="#">AS</a></li>
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
