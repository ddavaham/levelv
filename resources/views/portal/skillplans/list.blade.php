@extends('layout.index')

@section('title', config('app.name'). " Skillplans")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mt-3">{{ config('app.name') }} Skillplan's</h3>
                <hr />
                @include('extra.alert')
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                @include('portal.extra.dashboard')
            </div>
            <div class="col-md-8">
                <h3 class="text-center">Skillplan List</h3>
                <hr />
                <div class="list-group">
                    @forelse ($skillplans as $plan)
                        <a href="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" class="list-group-item list-group-item-action">
                            <div class="media mt-0">
                                {{-- <img src="{{ config('services.eve.urls.img') }}/Type/{{ Auth::user()->id }}_64.jpg" class="rounded img-fluid mr-3" /> --}}
                                <div class="media-body align-center">
                                    <h4>{{ $plan->name }}</h4>
                                    <p>
                                        {{ $plan->description }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <li class="list-group-item">Unfortunately, there are no skillplans to display at this time. Start one today below using the button below.</li>
                    @endforelse

                </div>
                <button type="button" class="btn btn-primary mt-4" data-toggle="collapse" data-target="#startSkillplan">Start New Skillplan</button>
                <div id="startSkillplan" class="collapse">
                    @if(!Auth::check())
                        <div class="card mt-2">
                            <div class="card-body">
                                Sorry, you must be an authenticated user to create a skillplan.
                            </div>
                        </div>
                    @else
                        <form action="" method="post" class="mt-2">
                            <div class="form-group">
                                <label for="name">Skillplan Name:</label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Skillplan Name" required />
                            </div>
                            <div class="form-group">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-primary">Start Skillplan</button>
                                <button type="button" class="btn btn-danger" data-toggle="collapse" data-target="#startSkillplan">Nevermind, close this module</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>

    </script>
@endsection
