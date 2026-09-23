@extends('teachers.layouts.app')

@section('title', 'Teachers Dashboard')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Teachers Dashboard</h2>
            </div>
        </div>
    </div>

    {{-- Top counters --}}
    <div class="row column1">
        <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
                <div class="couter_icon"><div><i class="fa fa-user yellow_color"></i></div></div>
                <div class="counter_no">
                    <div>
                        <p class="total_no text-dark">{{ Auth::user()->name }}</p>
                        <p class="head_couter text-dark">Welcome</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
                <div class="couter_icon"><div><i class="fa fa-folder-open-o blue1_color"></i></div></div>
                <div class="counter_no">
                    <div>
                        <p class="total_no text-dark">{{ number_format($totalProjects) }}</p>
                        <p class="head_couter text-dark">My Projects</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
                <div class="couter_icon"><div><i class="fa fa-book green_color"></i></div></div>
                <div class="counter_no">
                    <div>
                        <p class="total_no text-dark">{{ number_format($totalProjectBooks) }}</p>
                        <p class="head_couter text-dark">Project Books</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
                <div class="couter_icon"><div><i class="fa fa-hourglass-half red_color"></i></div></div>
                <div class="counter_no">
                    <div>
                        <p class="total_no text-dark">{{ number_format($pendingMilestones) }}</p>
                        <p class="head_couter text-dark">Pending Milestones</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Project book status breakdown (scoped to this teacher's projects) --}}
    <div class="row column1 social_media_section">
        <div class="col-md-6 col-lg-3">
            <div class="full socile_icons fb margin_bottom_30">
                <div class="social_icon"><i class="fa fa-check-circle"></i></div>
                <div class="social_cont">
                    <ul>
                        <li>
                            <span><strong>{{ $projectBookStats['approved'] ?? 0 }}</strong></span>
                            <span>Approved</span>
                        </li>
                        <li>
                            <span><strong>{{ $projectBookStats['rejected'] ?? 0 }}</strong></span>
                            <span>Rejected</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="full socile_icons tw margin_bottom_30">
                <div class="social_icon"><i class="fa fa-hourglass-half"></i></div>
                <div class="social_cont">
                    <ul>
                        <li>
                            <span><strong>{{ $projectBookStats['pending'] ?? 0 }}</strong></span>
                            <span>Pending</span>
                        </li>
                        <li>
                            <span><strong>{{ $projectBookStats['cancelled'] ?? 0 }}</strong></span>
                            <span>Cancelled</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="full socile_icons linked margin_bottom_30">
                <div class="social_icon"><i class="fa fa-cogs"></i></div>
                <div class="social_cont">
                    <ul>
                        <li>
                            <span><strong>{{ $projectBookStats['working'] ?? 0 }}</strong></span>
                            <span>Working</span>
                        </li>
                        <li>
                            <span><strong>{{ $totalProjectBooks }}</strong></span>
                            <span>Total</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="full socile_icons google_p margin_bottom_30">
                <div class="social_icon"><i class="fa fa-trophy"></i></div>
                <div class="social_cont">
                    <ul>
                        <li>
                            <span><strong>{{ $projectBookStats['completed'] ?? 0 }}</strong></span>
                            <span>Completed</span>
                        </li>
                        <li>
                            <span><strong>{{ $totalProjects }}</strong></span>
                            <span>My Projects</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row column3">
        {{-- my projects --}}
        <div class="col-md-6">
            <div class="dark_bg full margin_bottom_30">
                <div class="full graph_head">
                    <div class="heading1 margin_0"><h2>My Assigned Projects</h2></div>
                </div>
                <div class="full graph_revenue">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="content testimonial">
                                @if($myProjects->isEmpty())
                                    <p class="testimonial">You have no assigned projects yet.</p>
                                @else
                                    <div id="testimonial_slider" class="carousel slide" data-ride="carousel">
                                        <div class="carousel-inner">
                                            @foreach($myProjects as $project)
                                                <div class="item carousel-item @if($loop->first) active @endif">
                                                    <div class="img-box"><i class="fa fa-folder-open" style="font-size:40px;"></i></div>
                                                    <p class="testimonial">
                                                        <strong>{{ $project->project_name }}</strong><br>
                                                        {{ \Illuminate\Support\Str::limit($project->project_topic, 90) }}
                                                    </p>
                                                    <p class="overview">
                                                        Group #{{ $project->group_number }} &middot; {{ ucfirst($project->status) }}
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                        <a class="carousel-control left carousel-control-prev" href="#testimonial_slider" data-slide="prev"><i class="fa fa-angle-left"></i></a>
                                        <a class="carousel-control right carousel-control-next" href="#testimonial_slider" data-slide="next"><i class="fa fa-angle-right"></i></a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- end my projects --}}

        {{-- milestone progress --}}
        <div class="col-md-6">
            <div class="white_shd full margin_bottom_30">
                <div class="full graph_head">
                    <div class="heading1 margin_0"><h2>Milestone Status</h2></div>
                </div>
                <div class="full progress_bar_inner">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="progress_bar">
                                @forelse($milestoneStats as $stat)
                                    <span class="skill" style="width:{{ $stat['percent'] }}%;">{{ $stat['label'] }} <span class="info_valume">{{ $stat['percent'] }}%</span></span>
                                    <div class="progress skill-bar">
                                        <div class="progress-bar progress-bar-animated progress-bar-striped" role="progressbar" aria-valuenow="{{ $stat['percent'] }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $stat['percent'] }}%;">
                                        </div>
                                    </div>
                                @empty
                                    <p>No milestones recorded yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- end milestone progress --}}
    </div>

    <div class="row column4 graph">
        {{-- today's tasks --}}
        <div class="col-md-6">
            <div class="dash_blog">
                <div class="dash_blog_inner">
                    <div class="dash_head">
                        <h3><span><i class="fa fa-calendar"></i> {{ now()->format('j F Y') }}</span></h3>
                    </div>
                    <div class="list_cont">
                        <p>Today's Milestones for {{ Auth::user()->name }}</p>
                    </div>
                    <div class="task_list_main">
                        @if($todaysMilestones->isEmpty())
                            <p>No milestones are due today.</p>
                        @else
                            <ul class="task_list">
                                @foreach($todaysMilestones as $milestone)
                                    <li>
                                        <a href="#">{{ $milestone->title }} &mdash; {{ $milestone->project->project_name ?? 'N/A' }}</a><br>
                                        <strong>{{ optional($milestone->tentative_time)->format('h:i A') }}</strong>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="read_more">
                        <div class="center"><a class="main_bt read_bt" href="{{ route('teachers.milestones.index') }}">Read More</a></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- recent updates --}}
        <div class="col-md-6">
            <div class="dash_blog">
                <div class="dash_blog_inner">
                    <div class="dash_head">
                        <h3><span><i class="fa fa-comments-o"></i> Updates</span></h3>
                    </div>
                    <div class="list_cont">
                        <p>Recent milestone activity on your projects</p>
                    </div>
                    <div class="msg_list_main">
                        @if($recentUpdates->isEmpty())
                            <p>No recent activity.</p>
                        @else
                            <ul class="msg_list">
                                @foreach($recentUpdates as $update)
                                    <li>
                                        <span><i class="fa fa-user-circle" style="font-size:32px;"></i></span>
                                        <span>
                                            <span class="name_user">{{ $update->project->project_name ?? 'N/A' }}</span>
                                            <span class="msg_user">
                                                {{ $update->title }} &mdash; {{ ucwords(str_replace('_', ' ', $update->status)) }}
                                            </span>
                                            <span class="time_ago">{{ $update->updated_at->diffForHumans() }}</span>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="read_more">
                        <div class="center"><a class="main_bt read_bt" href="{{ route('teachers.milestones.index') }}">Read More</a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection