<!-- app-Header -->
<div class="app-header header sticky">
    <div class="container-fluid main-container">
        <div class="d-flex">
            <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript:void(0)"></a>
            <!-- sidebar-toggle-->
            <a class="logo-horizontal " href="index.html">
                <img src="assets/images/brand/logo.png" class="header-brand-img desktop-logo" alt="logo">
                <img src="assets/images/brand/logo-3.png" class="header-brand-img light-logo1" alt="logo">
            </a>
            <!-- LOGO -->

            <div class="d-flex order-lg-2 ms-auto header-right-icons"> 
                <div class="navbar navbar-collapse responsive-navbar p-0">
                    <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                        <div class="d-flex order-lg-2">

                            <!-- Theme toggle -->
                            <div class="dropdown  d-flex">
                                <a class="nav-link icon theme-layout nav-link-bg layout-setting">
                                    <span class="dark-layout"><i class="fe fe-moon"></i></span>
                                    <span class="light-layout"><i class="fe fe-sun"></i></span>
                                </a>
                            </div>

                            <!-- Fullscreen -->
                            <div class="dropdown d-flex">
                                <a class="nav-link icon full-screen-link nav-link-bg">
                                    <i class="fe fe-minimize fullscreen-button"></i>
                                </a>
                            </div>

                            <!-- Profile Dropdown -->
                            <div class="dropdown d-flex profile-1">
                                <a href="javascript:void(0)" data-bs-toggle="dropdown" class="nav-link leading-none d-flex">
                                    <img 
                                        src="{{ auth()->guard('admin')->user()->image 
                                                ? asset('storage/images/user_image/' . auth()->guard('admin')->user()->image) 
                                                : asset('assets/images/users/7.png') }}" 
                                        alt="profile-user"
                                        class="avatar profile-user brround cover-image"> 
                                </a>

                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <div class="drop-heading">
                                        <div class="text-center">
                                            <h5 class="text-dark mb-0 fs-14 fw-semibold">{{ Auth::guard('admin')->user()->name }}</h5>
                                        </div>
                                    </div>

                                    <div class="dropdown-divider m-0"></div>

                                    <!-- Sign out with SweetAlert -->
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="confirmAdminLogout()"> 
                                        <i class="dropdown-icon fe fe-alert-circle"></i> Sign out
                                    </a>

                                    <!-- Hidden logout form -->
                                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                            <!-- /Profile Dropdown -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /app-Header -->


