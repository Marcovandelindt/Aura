<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('home.index') }}" class="sidebar-brand">
            <span class="brand-icon">A</span>
            <span class="brand-text">Aura</span>
        </a>
    </div>
    
    <div class="sidebar-content">
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('home.index') }}" class="nav-link {{ request()->routeIs('home.index') ? 'active' : '' }}">
                    <i class="fas fa-home nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Lifestyle</span>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('music.index') }}" class="nav-link {{ request()->routeIs('music.index') ? 'active' : '' }}">
                    <i class="fas fa-music nav-icon"></i>
                    <span class="nav-text">Music</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('music.stats') }}" class="nav-link {{ request()->routeIs('music.stats') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar nav-icon"></i>
                    <span class="nav-text">Music Stats</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('music.all-time') }}" class="nav-link {{ request()->routeIs('music.all-time') ? 'active' : '' }}">
                    <i class="fas fa-crown nav-icon"></i>
                    <span class="nav-text">All-Time Favorieten</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('lastfm.index') }}" class="nav-link {{ request()->routeIs('lastfm.*') ? 'active' : '' }}">
                    <i class="fas fa-record-vinyl nav-icon"></i>
                    <span class="nav-text">Last.fm</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('playstation.index') }}" class="nav-link {{ request()->routeIs('playstation.*') ? 'active' : '' }}">
                    <i class="fas fa-gamepad nav-icon"></i>
                    <span class="nav-text">PlayStation</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('nintendo-switch.index') }}" class="nav-link {{ request()->routeIs('nintendo-switch.*') ? 'active' : '' }}">
                    <i class="fas fa-gamepad nav-icon"></i>
                    <span class="nav-text">Nintendo Switch</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('steam.index') }}" class="nav-link {{ request()->routeIs('steam.*') ? 'active' : '' }}">
                    <i class="fab fa-steam nav-icon"></i>
                    <span class="nav-text">Steam</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('backlog.index') }}" class="nav-link {{ request()->routeIs('backlog.*') ? 'active' : '' }}">
                    <i class="fas fa-bookmark nav-icon"></i>
                    <span class="nav-text">Backlog</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('movies.index') }}" class="nav-link {{ request()->routeIs('movies.*') ? 'active' : '' }}">
                    <i class="fas fa-film nav-icon"></i>
                    <span class="nav-text">Movies</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('tv.index') }}" class="nav-link {{ request()->routeIs('tv.*') ? 'active' : '' }}">
                    <i class="fas fa-tv nav-icon"></i>
                    <span class="nav-text">TV Series</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('journal.index') }}" class="nav-link {{ request()->routeIs('journal.*') ? 'active' : '' }}">
                    <i class="fas fa-book nav-icon"></i>
                    <span class="nav-text">Journal</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-heart nav-icon"></i>
                    <span class="nav-text">Mood Tracker</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('health.index') }}" class="nav-link {{ request()->routeIs('health.*') ? 'active' : '' }}">
                    <i class="fas fa-heartbeat nav-icon"></i>
                    <span class="nav-text">Health</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('strava.index') }}" class="nav-link {{ request()->routeIs('strava.*') ? 'active' : '' }}">
                    <i class="fas fa-running nav-icon"></i>
                    <span class="nav-text">Strava</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <i class="fas fa-wallet nav-icon"></i>
                    <span class="nav-text">Uitgaven</span>
                </a>
            </li>

            <li class="nav-section">
                <span class="nav-section-title">Productivity</span>
            </li>

            <li class="nav-item">
                <a href="{{ route('jobs.index') }}" class="nav-link {{ request()->routeIs('jobs.*') ? 'active' : '' }}">
                    <i class="fas fa-briefcase nav-icon"></i>
                    <span class="nav-text">Job Applications</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('weekly.index') }}" class="nav-link {{ request()->routeIs('weekly.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-week nav-icon"></i>
                    <span class="nav-text">Weekly</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-tasks nav-icon"></i>
                    <span class="nav-text">Tasks</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-calendar-alt nav-icon"></i>
                    <span class="nav-text">Calendar</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Development</span>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('ideas.index') }}" class="nav-link {{ request()->routeIs('ideas.*') ? 'active' : '' }}">
                    <i class="fas fa-lightbulb nav-icon"></i>
                    <span class="nav-text">Ideas</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Settings</span>
            </li>

            <li class="nav-item">
                <a href="{{ route('settings.general') }}" class="nav-link {{ request()->routeIs('settings.general*') ? 'active' : '' }}">
                    <i class="fas fa-cog nav-icon"></i>
                    <span class="nav-text">General</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('settings.moods') }}" class="nav-link {{ request()->routeIs('settings.moods*') ? 'active' : '' }}">
                    <i class="fas fa-heart nav-icon"></i>
                    <span class="nav-text">Moods</span>
                </a>
            </li>
            
            <!-- Theme Switcher -->
            <li class="nav-item theme-switcher">
                <div class="nav-link-static">
                    <i class="fas fa-palette nav-icon"></i>
                    <span class="nav-text">Theme</span>
                </div>
                <div class="theme-options">
                    @foreach($availableThemes as $themeKey => $themeInfo)
                        <button type="button" 
                                class="theme-option {{ $currentTheme === $themeKey ? 'active' : '' }}" 
                                onclick="switchTheme('{{ $themeKey }}')"
                                title="{{ $themeInfo['description'] }}">
                            <i class="{{ $themeInfo['icon'] }}"></i>
                            <span>{{ $themeInfo['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-sign-out-alt nav-icon"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>