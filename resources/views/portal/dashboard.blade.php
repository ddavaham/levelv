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
                @include('portal.extra.dashboard')
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
                            <div class="float-right">
                                <span id="countFinished">{{ $jobs->get('finished') }}</span>
                            </div>
                            Completed Jobs
                        </li>
                        <li class="list-group-item">
                            <div class="float-right">
                                <span id="countFailed">{{ $jobs->get('failed') }}</span>
                            </div>
                             Jobs That Failed
                        </li>
                        <li class="list-group-item">
                            <em>This module updates every {{ config('services.eve.updateInterval') }} seconds</em><br>
                            <em>This module only reflects the job count of the currently logged in character</em>
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
                                        {{ $alt->info->name }} @if (Auth::user()->id == $alt->id) {{ "[Main]" }} @else {{ "[Alt]" }} @endif
                                    </h4>
                                    <p>
                                        {{ $alt->info->corporation->name }} / @if(!is_null($alt->info->alliance)) {{ $alt->info->alliance->name }} @endif
                                    </p>
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
        @if (Auth::user()->jobs->where('status', 'queued')->count() > 0)
            interval = {{ config('services.eve.updateInterval') * 1000 }};
            function updateJobs() {
                $.ajax({
                    url: "{{ route('api.jobs.status', ['id' => Auth::user()->id]) }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (data, textStatus, request) {
                        console.log(data)
                        document.getElementById('countPending').innerHTML = data.pending;
                        document.getElementById('countFinished').innerHTML = data.finished;
                        document.getElementById('countFailed').innerHTML = data.failed;
                        if (data.pending == 0) {
                            clearInterval(update);
                        }
                    }
                });
            };

            $(document).ready(function ()  {
                update = setInterval(updateJobs, interval);
            });
        @endif
    </script>
@endsection
