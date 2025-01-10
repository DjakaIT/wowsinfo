<header>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow4">
		<a class="navbar-brand" href="/">
			<img src="{{ asset('images/logo-white.png') }}" alt="logo">
		</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav-collapse" aria-controls="nav-collapse" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="nav-collapse">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item">
					<a class="nav-link" href="/home">nav_home</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="/wiki">nav_wiki</a>
				</li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						nav_lang
					</a>
					<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
						<li><a class="dropdown-item" href="#" >KEY1 VALUE1</a></li>
						<li><a class="dropdown-item" href="#" >KEY2 VALUE2</a></li>
					</ul>
				</li>
			</ul>

			<!-- Right-aligned nav items -->
			 
			<ul class="navbar-nav">
				<li class="nav-item">
					<div class="input-group">
						<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Server</button>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="#">EU</a></li>
							<li><a class="dropdown-item" href="#">NA</a></li>
							<li><a class="dropdown-item" href="#">AS</a></li>
							<li><hr class="dropdown-divider"></li>
							<li><a class="dropdown-item disabled" href="#">RU</a></li>
						</ul>
						<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Dropdown</button>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="#">Player</a></li>
							<li><a class="dropdown-item" href="#">Clan</a></li>
						</ul>
						<input type="text" class="form-control" aria-label="Text input with dropdown button">
						<button class="btn btn-outline-secondary" type="button" id="button-addon2">Button</button>
					</div>
				</li>

				<li class="nav-item" v-if="get_user.name == ''">
					<a class="nav-link" href="/login">nav_login</a>
				</li>

				<li class="nav-item dropdown" v-else>
					<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<em>get_user.name</em>
					</a>
					<ul class="dropdown-menu" aria-labelledby="userDropdown">
						<li><a class="dropdown-item" href="/dashboard">nav_dashboard</a></li>
						<li><a class="dropdown-item" href="#">nav_logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</nav>
</header>