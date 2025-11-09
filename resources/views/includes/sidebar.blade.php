 

<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            <a class="header-brand1" href="{{route('dashboard')}}">
                <img src="{{asset('assets/images/brand/logo.png')}}" class="header-brand-img desktop-logo" alt="logo">
                <img src="{{asset('assets/images/brand/logo-1.png')}}" class="header-brand-img toggle-logo" alt="logo">
                <img src="{{asset('assets/images/brand/logo-2.png')}}" class="header-brand-img light-logo" alt="logo">
                <img src="{{asset('assets/images/brand/logo-3.png')}}" class="header-brand-img light-logo1" alt="logo">
            </a>
        </div>
        <!-- Profile Section with Stats -->
        <div class="profile-section-sash">
            <img src="{{Auth::user()->image ? asset('storage/images/user_image/'.@Auth::user()->image) : asset('assets/images/users/7.png') }}" class="profile-pic" alt="Profile">
            <div class="profile-name">{{ Auth::user() ? Auth::user()->name : '' }}</div>
            <div class="profile-location">{{ Auth::user() ? Auth::user()->city : '' }}</div>
            <div class="profile-stats-row">
                <div class="profile-stats-block">
                    <div class="count">
                        {{@$totalPosts}}
                    </div>
                    <div class="label">Posts</div>
                </div>
                <div class="profile-stats-block">
                    <div class="count">
                        {{@$totalLikes}}
                    </div>
                    <div class="label">Likes</div>
                </div>
            </div>
        </div>
        <!-- Main Menu -->
        <div class="main-sidemenu">
            <ul class="side-menu">
                <li class="slide">
                    <a class="side-menu__item {{Route::is('dashboard') ? 'active' : ''}}" href="{{route('dashboard')}}">
                        <i class="side-menu__icon fe fe-home"></i>
                        <span class="side-menu__label">Feed</span>
                    </a>
                </li>
                <!-- You can add other menu items below as needed -->
            </ul>
        </div>
    </div>
</div>
