{{-- vars: $updates, $href, $byWhom (bool: show who did it instead of the project) --}}
<x-dx.panel title="Recent activity" sub="Latest milestone changes" :href="$href ?? null">
    @if($updates->isEmpty())
        <x-dx.empty icon="comments-o" title="No activity yet" />
    @else
        <ul class="dx-list">
            @foreach($updates as $u)
                <li>
                    <span class="dx-avatar" style="margin-top:2px">{{ strtoupper(mb_substr($u->doneBy->name ?? '?', 0, 1)) }}</span>
                    <div class="dx-list__main">
                        <div class="dx-list__title">{{ $u->title }}</div>
                        <div class="dx-list__sub">
                            {{ !empty($byWhom) ? ($u->doneBy->name ?? 'Unassigned') . ' on ' : '' }}{{ $u->project->project_name ?? 'N/A' }}
                        </div>
                        <x-dx.pill :status="$u->status" />
                    </div>
                    <span class="dx-when">{{ $u->updated_at->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</x-dx.panel>
