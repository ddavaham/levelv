<h5 class="text-center">Sub Navigation</h5>
<hr />
<div class="list-group">
    <a href="{{ route('overview', ['member' => $member]) }}" class="list-group-item list-group-item-action">My Skills</a>
    <a href="{{ route('queue', ['member' => $member]) }}" class="list-group-item list-group-item-action">My Queue</a>
    <a href="{{ route('attributes', ['member' => $member]) }}" class="list-group-item list-group-item-action">My Attributes & Implants</a>
    @if($scopes->containsStrict("esi-clones.read_clones.v1"))
        <a href="{{ route('clones', ['member' => $member]) }}" class="list-group-item list-group-item-action">My Jump Clones</a>
    @endif
    {{-- <a href="{{ route('fittings.list') }}" class="list-group-item list-group-item-action" target="_blank">Fitting Manager</a> --}}
</div>
