<nav id="sidebar">
    <div class="sidebar_blog_1">
        <div class="sidebar-header">
            <div class="logo_section">
                <a href="{{ route('teachers.dashboard') }}">
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

            <li class="{{ request()->routeIs('teachers.dashboard') ? 'active' : '' }}">
                <a href="{{ route('teachers.dashboard') }}">
                    <i class="fa fa-dashboard orange_color"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('teachers.projects.*') ? 'active' : '' }}">
                <a href="#teacher_projects_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('teachers.projects.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-folder-open blue1_color"></i> <span>My Projects</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.projects.*') ? 'show' : '' }}" id="teacher_projects_menu">
                    <li class="{{ request()->routeIs('teachers.projects.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.projects.index') }}">&gt; <span>All My Projects</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('teachers.project-books.*') ? 'active' : '' }}">
                <a href="#teacher_project_books_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('teachers.project-books.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-book purple_color"></i> <span>My Project Books</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.project-books.*') ? 'show' : '' }}" id="teacher_project_books_menu">
                    <li class="{{ request()->routeIs('teachers.project-books.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.project-books.index') }}">&gt; <span>All My Project Books</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('teachers.meetings.*') ? 'active' : '' }}">
                <a href="#teacher_meetings_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('teachers.meetings.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-calendar green_color"></i> <span>Meetings</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.meetings.*') ? 'show' : '' }}" id="teacher_meetings_menu">
                    <li class="{{ request()->routeIs('teachers.meetings.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.meetings.index') }}">&gt; <span>All Meetings</span></a>
                    </li>
                    <li class="{{ request()->routeIs('teachers.meetings.create') ? 'active' : '' }}">
                        <a href="{{ route('teachers.meetings.create') }}">&gt; <span>Add New Meeting</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('teachers.milestones.*') ? 'active' : '' }}">
                <a href="#teacher_milestones_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('teachers.milestones.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-flag-checkered yellow_color"></i> <span>Project Milestones</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.milestones.*') ? 'show' : '' }}" id="teacher_milestones_menu">
                    <li class="{{ request()->routeIs('teachers.milestones.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.milestones.index') }}">&gt; <span>All Project Milestones</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('teachers.presentations.*') ? 'active' : '' }}">
                <a href="#teacher_presentations_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('teachers.presentations.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-file-powerpoint-o orange_color"></i> <span>Project Presentations</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.presentations.*') ? 'show' : '' }}" id="teacher_presentations_menu">
                    <li class="{{ request()->routeIs('teachers.presentations.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.presentations.index') }}">&gt; <span>All Project Presentations</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('teachers.video-resumes.*') ? 'active' : '' }}">
                <a href="#teacher_video_resumes_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('teachers.video-resumes.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-video-camera blue_color"></i> <span>Project Video Resumes</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.video-resumes.*') ? 'show' : '' }}" id="teacher_video_resumes_menu">
                    <li class="{{ request()->routeIs('teachers.video-resumes.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.video-resumes.index') }}">&gt; <span>All Project Video Resumes</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('teachers.project-marks.*') ? 'active' : '' }}">
                <a href="#teacher_project_marks_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('teachers.project-marks.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-star yellow_color"></i> <span>Project Marks</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('teachers.project-marks.*') ? 'show' : '' }}" id="teacher_project_marks_menu">
                    <li class="{{ request()->routeIs('teachers.project-marks.index') ? 'active' : '' }}">
                        <a href="{{ route('teachers.project-marks.index') }}">&gt; <span>All Project Marks</span></a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
