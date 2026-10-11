{{-- vars: $projects, $title, $sub, $href, $showSupervisor (bool), $emptyTitle, $emptyText --}}
<x-dx.panel :title="$title" :sub="$sub ?? null" :href="$href ?? null">
    @forelse($projects as $p)
        <article class="dx-project dx-st-{{ $p['status'] }}">
            <div class="dx-project__top">
                <div>
                    <h4>{{ $p['name'] }}</h4>
                    <div class="dx-project__meta">
                        <span>Group {{ $p['group'] }}</span>
                        @if($p['topic'])<span>{{ \Illuminate\Support\Str::limit($p['topic'], 40) }}</span>@endif
                        @if(!empty($showSupervisor))
                            @if($p['teacher'])<span>Supervisor: {{ $p['teacher'] }}</span>
                            @else<span class="is-warn">No supervisor assigned</span>@endif
                        @endif
                    </div>
                </div>
                <x-dx.pill :status="$p['status']" />
            </div>

            <div class="dx-track" role="img" aria-label="{{ $p['progress'] }}% of milestones completed{{ $p['elapsed'] !== null ? ', ' . $p['elapsed'] . '% of the time used' : '' }}">
                <div class="dx-track__fill" style="width: {{ $p['progress'] }}%"></div>
                @if($p['elapsed'] !== null)<span class="dx-track__tick" style="left: calc({{ $p['elapsed'] }}% - 1px)"></span>@endif
            </div>

            <div class="dx-project__foot">
                <span>
                    @if($p['total'] > 0)
                        {{ $p['done'] }} of {{ $p['total'] }} milestones done
                    @else
                        No milestones yet
                    @endif
                </span>
                <span class="{{ $p['behind'] ? 'is-warn' : '' }}">
                    @if($p['behind'])Behind schedule, @endif
                    @if($p['daysLeft'] === null)
                        No end date set
                    @elseif($p['daysLeft'] >= 0)
                        {{ $p['daysLeft'] }} {{ \Illuminate\Support\Str::plural('day', $p['daysLeft']) }} left
                    @else
                        {{ abs($p['daysLeft']) }} {{ \Illuminate\Support\Str::plural('day', abs($p['daysLeft'])) }} past end date
                    @endif
                </span>
                @if(count($p['team']))
                    <span class="dx-avatars" title="{{ implode(', ', $p['team']) }}">
                        @foreach(array_slice($p['team'], 0, 4) as $member)
                            <span class="dx-avatar">{{ strtoupper(mb_substr($member, 0, 1)) }}</span>
                        @endforeach
                        @if(count($p['team']) > 4)<span class="dx-avatar">+{{ count($p['team']) - 4 }}</span>@endif
                    </span>
                @endif
            </div>
        </article>
    @empty
        <x-dx.empty icon="folder-open-o" :title="$emptyTitle ?? 'No projects yet'" :text="$emptyText ?? null" />
    @endforelse

    @if($projects->isNotEmpty())
        <div class="dx-key">
            <span><i class="k-bar"></i>Milestones completed</span>
            <span><i class="k-tick"></i>Share of project time used</span>
        </div>
    @endif
</x-dx.panel>
