@extends('layout.index')

@section('title', Auth::user()->info->name . " Dashboard")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center">Welcome to your {{ config('app.name') }} Dashboard</h1>
                <hr />
                @include('extra.alert')
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                Below is a list of your characters. To view the character, click on the eye.
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <h3>Character List</h3>
                <hr />
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="float-right">
                            <a href="{{ route('overview', ['id' => Auth::user()->id]) }}" class="btn btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        <div class="media mt-0">
                            <img src="{{ config('services.eve.urls.img') }}/Character/{{ Auth::user()->id }}_64.jpg" class="rounded img-fluid mr-3" />
                            <div class="media-body align-center">
                                <h4>{{ Auth::user()->info->name }}</h4>
                                <p>
                                    {{ Auth::user()->info->corporation->name }} / @if(!is_null(Auth::user()->info->alliance)) {{ Auth::user()->info->alliance->name }} @endif
                                </p>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="row">
                    <div class="col-12 mt-3">
                        <a href="#" class="btn btn-info float-right">Add Character</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Job Status
                    </div>
                    <div class="list-group">
                        <li class="list-group-item">
                            <div class="float-right">
                                <span id="countPending">{{ Auth::user()->jobs->whereIn('status', ['queued', 'executing'])->count() }}</span>
                            </div>
                            Pending Jobs
                        </li>
                        <li class="list-group-item">
                            <div class="float-right">
                                <span id="countFinished">{{ Auth::user()->jobs->whereIn('status', ['finished'])->count() }}</span>
                            </div>
                            Completed Jobs
                        </li>
                        <li class="list-group-item">
                            <div class="float-right">
                                <span id="countFailed">{{ Auth::user()->jobs->whereIn('status', ['failed'])->count() }}</span>
                            </div>
                             Jobs That Failed
                        </li>
                        <li class="list-group-item text-center">
                            <em>This module updates every {{ config('services.eve.updateInterval') }} seconds</em>
                        </li>
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
