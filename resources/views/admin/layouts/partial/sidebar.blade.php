<nav id="sidebar">
    <div class="sidebar_blog_1">
        <div class="sidebar-header">
            <div class="logo_section">
                <a href="{{ route('admin.dashboard') }}">
                    <img class="logo_icon img-responsive" src="{{ asset('images/logo/logo.webp') }}" alt="Logo" />
                </a>
            </div>
        </div>
        <div class="sidebar_user_info">
            <div class="icon_setting"></div>
            <div class="user_profle_side">
                <div class="user_img">
                    <img class="img-responsive" src="{{ asset('images/logo/logo.webp') }}" alt="{{ auth()->user()->name ?? 'User' }}" />
                </div>
                <div class="user_info">
                    <h6>{{ auth()->user()->name ?? 'Guest' }}</h6>
                    <p><span class="online_animation"></span> Online</p>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar_blog_2">
        <h4>General</h4>
        <ul class="list-unstyled components">

            <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="fa fa-dashboard orange_color"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <a href="#users_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('admin.users.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-users green_color"></i> <span>Users</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.users.*') ? 'show' : '' }}" id="users_menu">
                    <li class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}">&gt; <span>All Users</span></a>
                    </li>
                    <li class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.create') }}">&gt; <span>Add New User</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                <a href="#projects_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('admin.projects.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-folder-open blue1_color"></i> <span>Projects</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.projects.*') ? 'show' : '' }}" id="projects_menu">
                    <li class="{{ request()->routeIs('admin.projects.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.projects.index') }}">&gt; <span>All Projects</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('admin.project-books.*') ? 'active' : '' }}">
                <a href="#project_books_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('admin.project-books.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-book purple_color"></i> <span>Project Books</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.project-books.*') ? 'show' : '' }}" id="project_books_menu">
                    <li class="{{ request()->routeIs('admin.project-books.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.project-books.index') }}">&gt; <span>All Project Books</span></a>
                    </li>
                    <li class="{{ request()->routeIs('admin.project-books.create') ? 'active' : '' }}">
                        <a href="{{ route('admin.project-books.create') }}">&gt; <span>Add New Project Book</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('admin.meetings.*') ? 'active' : '' }}">
                <a href="#meetings_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('admin.meetings.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-calendar green_color"></i> <span>Meetings</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.meetings.*') ? 'show' : '' }}" id="meetings_menu">
                    <li class="{{ request()->routeIs('admin.meetings.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.meetings.index') }}">&gt; <span>All Meetings</span></a>
                    </li>
                    <li class="{{ request()->routeIs('admin.meetings.create') ? 'active' : '' }}">
                        <a href="{{ route('admin.meetings.create') }}">&gt; <span>Add New Meeting</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('admin.milestones.*') ? 'active' : '' }}">
                <a href="#milestones_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('admin.milestones.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-flag-checkered yellow_color"></i> <span>Milestones</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.milestones.*') ? 'show' : '' }}" id="milestones_menu">
                    <li class="{{ request()->routeIs('admin.milestones.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.milestones.index') }}">&gt; <span>All Milestones</span></a>
                    </li>
                    <li class="{{ request()->routeIs('admin.milestones.create') ? 'active' : '' }}">
                        <a href="{{ route('admin.milestones.create') }}">&gt; <span>Add New Milestone</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('admin.presentations.*') ? 'active' : '' }}">
                <a href="#presentations_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('admin.presentations.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-file-powerpoint-o orange_color"></i> <span>Presentations</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.presentations.*') ? 'show' : '' }}" id="presentations_menu">
                    <li class="{{ request()->routeIs('admin.presentations.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.presentations.index') }}">&gt; <span>All Presentations</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('admin.video-resumes.*') ? 'active' : '' }}">
                <a href="#video_resumes_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('admin.video-resumes.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-video-camera blue_color"></i> <span>Video Resumes</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.video-resumes.*') ? 'show' : '' }}" id="video_resumes_menu">
                    <li class="{{ request()->routeIs('admin.video-resumes.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.video-resumes.index') }}">&gt; <span>All Video Resumes</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('admin.project-marks.*') ? 'active' : '' }}">
                <a href="#project_marks_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('admin.project-marks.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-star yellow_color"></i> <span>Project Marks</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('admin.project-marks.*') ? 'show' : '' }}" id="project_marks_menu">
                    <li class="{{ request()->routeIs('admin.project-marks.index') ? 'active' : '' }}">
                        <a href="{{ route('admin.project-marks.index') }}">&gt; <span>All Project Marks</span></a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
