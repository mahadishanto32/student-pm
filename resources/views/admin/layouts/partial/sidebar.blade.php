<nav id="sidebar">
    <div class="sidebar_blog_1">
        <div class="sidebar-header">
            <div class="logo_section">
                <a href="{{ route('admin.dashboard') }}"><img class="logo_icon img-responsive" src="{{ asset('images/logo/logo_icon.png') }}" alt="#" /></a>
            </div>
        </div>
        <div class="sidebar_user_info">
            <div class="icon_setting"></div>
            <div class="user_profle_side">
                <div class="user_img"><img class="img-responsive" src="{{ asset('images/layout_img/user_img.jpg') }}" alt="#" /></div>
                <div class="user_info">
                    <h6>{{ auth()->user()->name ?? 'John David' }}</h6>
                    <p><span class="online_animation"></span> Online</p>
                </div>
            </div>
        </div>
    </div>
    <div class="sidebar_blog_2">
        <h4>General</h4>
        <ul class="list-unstyled components">
            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="#dashboard" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-dashboard yellow_color"></i> <span>Dashboard</span></a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.dashboard') ? 'show' : '' }}" id="dashboard">
                    <li>
                        <a href="{{ route('admin.dashboard') }}">&gt; <span>Default Dashboard</span></a>
                    </li>
                    <li>
                        <a href="#">&gt; <span>Dashboard style 2</span></a>
                    </li>
                </ul>
            </li>
            <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a href="#users_menu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-users green_color"></i> <span>Users</span></a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.users.*') ? 'show' : '' }}" id="users_menu">
                    <li class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}">&gt; <span>All Users</span></a>
                    </li>
                    <li class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.create') }}">&gt; <span>Add New User</span></a>
                    </li>
                </ul>
            </li>
            <li><a href="#"><i class="fa fa-clock-o orange_color"></i> <span>Widgets</span></a></li>
            <li>
                <a href="#element" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-diamond purple_color"></i> <span>Elements</span></a>
                <ul class="collapse list-unstyled" id="element">
                    <li><a href="#">&gt; <span>General Elements</span></a></li>
                    <li><a href="#">&gt; <span>Media Gallery</span></a></li>
                    <li><a href="#">&gt; <span>Icons</span></a></li>
                    <li><a href="#">&gt; <span>Invoice</span></a></li>
                </ul>
            </li>
            <li class="{{ request()->routeIs('table') ? 'active' : '' }}"><a href="{{ \Illuminate\Support\Facades\Route::has('table') ? route('table') : '#' }}"><i class="fa fa-table purple_color2"></i> <span>Tables</span></a></li>
            <li>
                <a href="#apps" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-object-group blue2_color"></i> <span>Apps</span></a>
                <ul class="collapse list-unstyled" id="apps">
                    <li><a href="#">&gt; <span>Email</span></a></li>
                    <li><a href="#">&gt; <span>Calendar</span></a></li>
                    <li><a href="#">&gt; <span>Media Gallery</span></a></li>
                </ul>
            </li>
            <li><a href="#"><i class="fa fa-briefcase blue1_color"></i> <span>Pricing Tables</span></a></li>
            <li>
                <a href="#">
                    <i class="fa fa-paper-plane red_color"></i> <span>Contact</span></a>
            </li>
            <li>
                <a href="#additional_page" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-clone yellow_color"></i> <span>Additional Pages</span></a>
                <ul class="collapse list-unstyled" id="additional_page">
                    <li>
                        <a href="#">&gt; <span>Profile</span></a>
                    </li>
                    <li>
                        <a href="#">&gt; <span>Projects</span></a>
                    </li>
                    <li>
                        <a href="#">&gt; <span>Login</span></a>
                    </li>
                    <li>
                        <a href="#">&gt; <span>404 Error</span></a>
                    </li>
                </ul>
            </li>
            <li><a href="#"><i class="fa fa-map purple_color2"></i> <span>Map</span></a></li>
            <li><a href="#"><i class="fa fa-bar-chart-o green_color"></i> <span>Charts</span></a></li>
            <li><a href="#"><i class="fa fa-cog yellow_color"></i> <span>Settings</span></a></li>
        </ul>
    </div>
</nav>
