<nav id="sidebar">
    <div class="sidebar_blog_1">
        <div class="sidebar-header">
            <div class="logo_section">
                <a href="{{ route('dashboard') }}">
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

            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="fa fa-dashboard orange_color"></i> <span>Dashboard</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('student.projects.*') ? 'active' : '' }}">
                <a href="#student_projects_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('student.projects.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-folder-open blue1_color"></i> <span>My Projects</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('student.projects.*') ? 'show' : '' }}" id="student_projects_menu">
                    <li class="{{ request()->routeIs('student.projects.index') ? 'active' : '' }}">
                        <a href="{{ route('student.projects.index') }}">&gt; <span>All My Projects</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('student.project-books.*') ? 'active' : '' }}">
                <a href="#student_project_books_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('student.project-books.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-book purple_color"></i> <span>My Project Books</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('student.project-books.*') ? 'show' : '' }}" id="student_project_books_menu">
                    <li class="{{ request()->routeIs('student.project-books.index') ? 'active' : '' }}">
                        <a href="{{ route('student.project-books.index') }}">&gt; <span>All My Project Books</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('student.meetings.*') ? 'active' : '' }}">
                <a href="#student_meetings_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('student.meetings.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-calendar green_color"></i> <span>My Meetings</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('student.meetings.*') ? 'show' : '' }}" id="student_meetings_menu">
                    <li class="{{ request()->routeIs('student.meetings.index') ? 'active' : '' }}">
                        <a href="{{ route('student.meetings.index') }}">&gt; <span>All My Meetings</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('student.milestones.*') ? 'active' : '' }}">
                <a href="#student_milestones_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('student.milestones.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-flag-checkered yellow_color"></i> <span>Project Milestones</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('student.milestones.*') ? 'show' : '' }}" id="student_milestones_menu">
                    <li class="{{ request()->routeIs('student.milestones.index') ? 'active' : '' }}">
                        <a href="{{ route('student.milestones.index') }}">&gt; <span>All Project Milestones</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('student.presentations.*') ? 'active' : '' }}">
                <a href="#student_presentations_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('student.presentations.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-file-powerpoint-o orange_color"></i> <span>Project Presentations</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('student.presentations.*') ? 'show' : '' }}" id="student_presentations_menu">
                    <li class="{{ request()->routeIs('student.presentations.index') ? 'active' : '' }}">
                        <a href="{{ route('student.presentations.index') }}">&gt; <span>All Project Presentations</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('student.video-resumes.*') ? 'active' : '' }}">
                <a href="#student_video_resumes_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('student.video-resumes.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-video-camera blue_color"></i> <span>Video Resumes</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('student.video-resumes.*') ? 'show' : '' }}" id="student_video_resumes_menu">
                    <li class="{{ request()->routeIs('student.video-resumes.index') ? 'active' : '' }}">
                        <a href="{{ route('student.video-resumes.index') }}">&gt; <span>All Video Resumes</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('student.project-marks.*') ? 'active' : '' }}">
                <a href="#student_project_marks_menu" data-toggle="collapse"
                   aria-expanded="{{ request()->routeIs('student.project-marks.*') ? 'true' : 'false' }}" class="dropdown-toggle">
                    <i class="fa fa-star yellow_color"></i> <span>Project Marks</span>
                </a>
                <ul class="collapse list-unstyled {{ request()->routeIs('student.project-marks.*') ? 'show' : '' }}" id="student_project_marks_menu">
                    <li class="{{ request()->routeIs('student.project-marks.index') ? 'active' : '' }}">
                        <a href="{{ route('student.project-marks.index') }}">&gt; <span>All Project Marks</span></a>
                    </li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <a href="{{ route('profile.edit') }}">
                    <i class="fa fa-user green_color"></i> <span>My Profile</span>
                </a>
            </li>

        </ul>
    </div>
</nav>
