@extends('layout.index')

@section('title', Auth::user()->info->name . " Dashboard")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mt-3">Welcome to your {{ config('app.name') }} Dashboard</h3>
                <hr />
                @include('extra.alert')
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                @include('portal.extra.account')
                <hr />
                <div class="card">
                    <div class="card-header">
                        Job Status
                    </div>
                    <div class="list-group">
                        <li class="list-group-item">
                            <div class="float-right">
                                <span id="countPending">{{ $jobs->get('pending') }}</span>
                            </div>
                            Pending Jobs
                        </li>
                        <li class="list-group-item">
                            <em>This module updates every {{ config('services.eve.updateInterval') }} seconds</em><br>
                        </li>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <h3 class="text-center">Character List</h3>
                <hr />
                <div class="list-group">
                    @foreach (Auth::user()->alts as $alt)
                        <a href="{{ route('overview', ['id' => $alt->id]) }}" class="list-group-item list-group-item-action">
                            <div class="media mt-0">
                                <img src="{{ config('services.eve.urls.img') }}/Character/{{ $alt->id }}_64.jpg" class="rounded img-fluid mr-3" />
                                <div class="media-body align-center">
                                    <h4>
                                        [{{ $alt->info->corporation->ticker }}] {{ $alt->info->name }} @if (Auth::user()->main == $alt->id) {{ "[Main]" }} @else {{ "[Alt]" }} @endif
                                    </h4>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-12 mt-3">
                        <a href="{{ route('welcome') }}" class="btn btn-info float-right">Add Character</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    <script>
        interval = {{ config('services.eve.updateInterval') * 1000 }};
        function updateJobs() {
            $.ajax({
                url: "{{ route('api.jobs.status', ['id' => Auth::user()->id]) }}",
                type: 'GET',
                dataType: 'json',
                success: function (data, textStatus, request) {
                    document.getElementById('countPending').innerHTML = data.pending;
                    if (data.pending == 0) {
                        clearInterval(update);
                    }
                }
            });
        };

        $(document).ready(function ()  {
            update = setInterval(updateJobs, interval);
        });
    </script>
@endsection
