<nav id="sidebar">
    <div class="sidebar_blog_1">
        <div class="sidebar-header">
            <div class="logo_section">
                <a href="{{ route('dashboard') }}">
                    <img class="logo_icon img-responsive"
                         src="{{ asset('images/logo/logo.webp') }}" alt="#" />
                </a>
            </div>
        </div>
        <div class="sidebar_user_info">
            <div class="icon_setting"></div>
            <div class="user_profle_side">
                <div class="user_img">
                    <img class="img-responsive"
                         src="{{ asset('images/logo/logo.webp') }}" alt="#" />
                </div>
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

            {{-- ---------- Dashboard ---------- --}}
            {{-- <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="#dashboard" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fa fa-dashboard yellow_color"></i> <span>Dashboard</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('dashboard') ? 'show' : '' }}"
                    id="dashboard">
                    <li><a href="{{ route('dashboard') }}">&gt; <span>Default Dashboard</span></a></li>
                    <li><a href="#">&gt; <span>Dashboard style 2</span></a></li>
                </ul>
            </li> --}}
            <li class="{{ request()->routeIs('teachers.dashboard') ? 'active' : '' }}"><a href="{{ route('teachers.dashboard') }}"><i class="fa fa-dashboard orange_color"></i> <span>Dashboard</span></a></li>

            {{-- ---------- Projects (role-aware) ---------- --}}
            <li class="{{ request()->routeIs('teachers.projects.*') ? 'active' : '' }}">
                <a href="#teacher_projects_menu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fa fa-briefcase blue1_color"></i> <span>My Projects</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.projects.*') ? 'show' : '' }}"
                    id="teacher_projects_menu">
                    <li class="{{ request()->routeIs('teachers.projects.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.projects.index') }}">&gt; <span>All My Projects</span></a>
                    </li>
                </ul>
            </li>

            {{-- ---------- Project Books (role-aware) ---------- --}}
            <li class="{{ request()->routeIs('teachers.project-books.*') ? 'active' : '' }}">
                <a href="#teacher_project_books_menu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fa fa-book blue1_color"></i> <span>My Project Books</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.project-books.*') ? 'show' : '' }}"
                    id="teacher_project_books_menu">
                    <li class="{{ request()->routeIs('teachers.projects.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.project-books.index') }}">&gt; <span>All My Projects Books</span></a>
                    </li>
                </ul>
            </li>

            {{-- ---------- Meetings ---------- --}}
            <li class="{{ request()->routeIs('teachers.meetings.*') ? 'active' : '' }}">
                <a href="#meetings_menu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fa fa-briefcase blue1_color"></i> <span>Meetings</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.meetings.*') ? 'show' : '' }}"
                    id="meetings_menu">
                    <li class="{{ request()->routeIs('teachers.meetings.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.meetings.index') }}">&gt; <span>All meetings</span></a>
                    </li>
                    <li class="{{ request()->routeIs('teachers.meetings.create') ? 'active' : '' }}">
                        <a href="{{ route('teachers.meetings.create') }}">&gt; <span>Add New Meetings</span></a>
                    </li>
                </ul>
            </li>

            {{-- ---------- Project MileStone (teachers) ---------- --}}
            <li class="{{ request()->routeIs('teachers.milestones.*') ? 'active' : '' }}">
                <a href="#teachers_milestones_menu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fa fa-briefcase green_color"></i> <span>Project MileStones</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.milestones.*') ? 'show' : '' }}"
                    id="teachers_milestones_menu">
                    <li class="{{ request()->routeIs('teachers.milestones.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.milestones.index') }}">&gt; <span>All Project MileStones</span></a>
                    </li>
                </ul>
            </li>

            {{-- ---------- Widgets ---------- --}}
            {{-- <li><a href="#"><i class="fa fa-clock-o orange_color"></i> <span>Widgets</span></a></li> --}}

            {{-- ---------- Elements ---------- --}}
            {{-- <li>
                <a href="#element" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fa fa-diamond purple_color"></i> <span>Elements</span>
                </a>
                <ul class="collapse list-unstyled" id="element">
                    <li><a href="#">&gt; <span>General Elements</span></a></li>
                    <li><a href="#">&gt; <span>Media Gallery</span></a></li>
                    <li><a href="#">&gt; <span>Icons</span></a></li>
                    <li><a href="#">&gt; <span>Invoice</span></a></li>
                </ul>
            </li> --}}

            {{-- ---------- Tables ---------- --}}
            {{-- <li class="{{ request()->routeIs('table') ? 'active' : '' }}">
                <a href="{{ \Illuminate\Support\Facades\Route::has('table') ? route('table') : '#' }}">
                    <i class="fa fa-table purple_color2"></i> <span>Tables</span>
                </a>
            </li> --}}

            {{-- ---------- Apps ---------- --}}
            {{-- <li>
                <a href="#apps" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fa fa-object-group blue2_color"></i> <span>Apps</span>
                </a>
                <ul class="collapse list-unstyled" id="apps">
                    <li><a href="#">&gt; <span>Email</span></a></li>
                    <li><a href="#">&gt; <span>Calendar</span></a></li>
                    <li><a href="#">&gt; <span>Media Gallery</span></a></li>
                </ul>
            </li> --}}

            {{-- <li><a href="#"><i class="fa fa-briefcase blue1_color"></i> <span>Pricing Tables</span></a></li>

            <li>
                <a href="#"><i class="fa fa-paper-plane red_color"></i> <span>Contact</span></a>
            </li> --}}

            {{-- ---------- Additional Pages ---------- --}}
            {{-- <li>
                <a href="#additional_page" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <i class="fa fa-clone yellow_color"></i> <span>Additional Pages</span>
                </a>
                <ul class="collapse list-unstyled" id="additional_page">
                    <li><a href="#">&gt; <span>Profile</span></a></li>
                    <li><a href="#">&gt; <span>Login</span></a></li>
                    <li><a href="#">&gt; <span>404 Error</span></a></li>
                </ul>
            </li>

            <li><a href="#"><i class="fa fa-map purple_color2"></i> <span>Map</span></a></li>
            <li><a href="#"><i class="fa fa-bar-chart-o green_color"></i> <span>Charts</span></a></li>
            <li><a href="#"><i class="fa fa-cog yellow_color"></i> <span>Settings</span></a></li> --}}

        </ul>
    </div>
</nav>