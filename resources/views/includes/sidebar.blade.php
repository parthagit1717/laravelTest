 
<div class="sticky">
<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
<div class="app-sidebar">
    <div class="side-header">
        <a class="header-brand1" href="{{route('dashboard')}}">
            <img src="{{asset('assets/images/brand/logo-2.png')}}" class="header-brand-img desktop-logo" alt="logo">
            <img src="{{asset('assets/images/brand/logo-1.png')}}" class="header-brand-img toggle-logo"
                alt="logo">
            <img src="{{asset('assets/images/brand/logo-1.png')}}" class="header-brand-img light-logo" alt="logo">
            <img src="{{asset('assets/images/brand/logo-2.png')}}" class="header-brand-img light-logo1"
                alt="logo">
        </a>
        <!-- LOGO -->
    </div>
    <div class="main-sidemenu">
        <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg"
                fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
            </svg></div>
        <ul class="side-menu">
            <li class="sub-category">
                <h3>Main</h3>
            </li>
            <li class="slide">
                <a class="side-menu__item {{Route::is('dashboard') ? 'active' : ''}}" data-bs-toggle="slide" href="{{route('dashboard')}}"><i
                        class="side-menu__icon fe fe-home"></i><span
                        class="side-menu__label">Dashboard</span></a>
            </li> 
             
           
            @if(Auth::user()->id==1)
                <li class="slide">
                    <a class="side-menu__item {{Route::is('manage_subs') ? 'active' : ''}}" data-bs-toggle="slide" href="{{route('manage_subs')}}"><i
                            class="side-menu__icon bi bi-credit-card"></i><span
                            class="side-menu__label">Manage Subscription</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{Route::is('manage.user','view_user') ? 'active' : ''}}" data-bs-toggle="slide" href="{{route('manage.user')}}"><i
                            class="side-menu__icon fe fe-users"></i><span
                            class="side-menu__label">Manage User</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{Route::is('transaction') ? 'active' : ''}}" data-bs-toggle="slide" href="{{route('transaction')}}"><i
                            class="side-menu__icon bi bi-cash"></i><span
                            class="side-menu__label">Transaction </span></a>
                </li>
            @endif
            @if(Auth::user()->user_type==3)
            <li class="slide">
                <a class="side-menu__item {{Route::is('subPlanList') ? 'active' : ''}}" data-bs-toggle="slide" href="{{route('subPlanList')}}"><i
                        class="side-menu__icon bi bi-credit-card"></i><span
                        class="side-menu__label">Subscription</span></a>
            </li>
             <li class="slide">
                <a class="side-menu__item {{Route::is('settings') ? 'active' : ''}}" data-bs-toggle="slide" href="{{route('settings')}}"><i
                        class="side-menu__icon bi bi-gear"></i><span
                        class="side-menu__label">Settings</span></a>
            </li> 
            @endif
            <!-- <li class="sub-category">
                <h3>UI Kit</h3>
            </li> -->
            <!-- <li class="slide">
                <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)"><i
                        class="side-menu__icon fe fe-slack"></i><span
                        class="side-menu__label">Apps</span><i
                        class="angle fe fe-chevron-right"></i></a>
                <ul class="slide-menu">
                    <li class="side-menu-label1"><a href="javascript:void(0)">Apps</a></li>
                    <li><a href="cards.html" class="slide-item"> Cards design</a></li>
                    <li><a href="calendar.html" class="slide-item"> Default calendar</a></li>
                    <li><a href="calendar2.html" class="slide-item"> Full calendar</a></li>
                    <li><a href="chat.html" class="slide-item"> Chat</a></li>
                    <li><a href="notify.html" class="slide-item"> Notifications</a></li>
                    <li><a href="sweetalert.html" class="slide-item"> Sweet alerts</a></li>
                    <li><a href="rangeslider.html" class="slide-item"> Range slider</a></li>
                    <li><a href="scroll.html" class="slide-item"> Content Scroll bar</a></li>
                    <li><a href="loaders.html" class="slide-item"> Loaders</a></li>
                    <li><a href="counters.html" class="slide-item"> Counters</a></li>
                    <li><a href="rating.html" class="slide-item"> Rating</a></li>
                    <li><a href="timeline.html" class="slide-item"> Timeline</a></li>
                    <li><a href="treeview.html" class="slide-item"> Treeview</a></li>
                    <li><a href="chart.html" class="slide-item"> Charts</a></li>
                    <li><a href="footers.html" class="slide-item"> Footers</a></li>
                    <li><a href="users-list.html" class="slide-item"> User List</a></li>
                    <li><a href="search.html" class="slide-item">Search</a></li>
                    <li><a href="crypto-currencies.html" class="slide-item"> Crypto-currencies</a></li>
                </ul>
            </li> -->
             
            <!-- <li class="sub-category">
                <h3>Pre-build Pages</h3>
            </li> -->
            
            <!-- <li class="slide">
                <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)"><i
                        class="side-menu__icon fe fe-shopping-bag"></i><span
                        class="side-menu__label">E-Commerce</span><i
                        class="angle fe fe-chevron-right"></i></a>
                <ul class="slide-menu">
                    <li class="side-menu-label1"><a href="javascript:void(0)">E-Commerce</a></li>
                    <li><a href="shop.html" class="slide-item"> Shop</a></li>
                    <li><a href="shop-description.html" class="slide-item"> Product Details</a></li>
                    <li><a href="cart.html" class="slide-item"> Shopping Cart</a></li>
                    <li><a href="add-product.html" class="slide-item"> Add Product</a></li>
                    <li><a href="wishlist.html" class="slide-item"> Wishlist</a></li>
                    <li><a href="checkout.html" class="slide-item"> Checkout</a></li>
                </ul>
            </li>  -->
             
             
             
        </ul>
        <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                width="24" height="24" viewBox="0 0 24 24">
                <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
            </svg></div>
    </div>
</div>
               