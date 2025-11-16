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
                <a href="#" class="nav-link">
                    <i class="fas fa-film nav-icon"></i>
                    <span class="nav-text">Movies & Shows</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="fas fa-heart nav-icon"></i>
                    <span class="nav-text">Mood Tracker</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span class="nav-section-title">Productivity</span>
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
                <a href="#" class="nav-link">
                    <i class="fas fa-cog nav-icon"></i>
                    <span class="nav-text">Settings</span>
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