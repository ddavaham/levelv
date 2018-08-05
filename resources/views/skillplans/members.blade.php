@extends('layout.index')

@section('title', $plan->name. " Skillplan")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 mt-3">
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 offset-md-2">

                <h3 class="text-center">
                    <div class="float-left">
                        <a href="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                    @if (Auth::user()->id == $plan->author_id)
                        <div class="float-right">
                            <a href="#" data-toggle="collapse" data-target="#addMembersCollapse">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    @endif
                    {{ $plan->name }} Members
                </h3>
                <hr />
                <div class="collapse {{ isset($results) ? "show" : "" }}" id="addMembersCollapse">
                    <form action="{{ route('skillplan.members', ['skillplan' => $plan->id]) }}" method="post">
                        <div class="row">
                            <div class="col-md-12">
                                <label for="entityToAdd">Type the name of the entity that you would like to add as a member:</label>
                                {{ csrf_field() }}
                                <div class="input-group">
                                    <input type="text" name="entityToAdd" id="entityToAdd" class="form-control" value="{{ old('entityToAdd') }}" placeholder="Enter Name of Character, Corporation, or Allinace to add" />
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-sm btn-secondary">Submit Request</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr />
                        @if(isset($results))
                            <h3>Search Results</h3>
                            <hr />
                            <div class="list-group">
                                @foreach($results as $result)
                                    <div class="list-group-item">
                                        <div class="media mt-0">
                                            <img src="{{ config('services.eve.urls.img') }}/{{ ucfirst($result->get('category')) }}/{{ $result->get('id') }}_64.{{ in_array($result->get('category'), ['corporation', 'alliance']) ? "png" : "jpg" }}" class="rounded img-fluid mr-3" />

                                            <div class="media-body">
                                                <div class="float-right">
                                                    <button type="submit" name="addEntity" value="{{ $result->get('id') ."::". $result->get('category') }}" class="btn btn-primary"><i class="fas fa-check"></i></button>
                                                </div>
                                                <h4>
                                                    {{ $result->get('name') }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <hr />
                        @endif
                    </form>
                </div>
                @include('extra.alert')
                @if ($members->count() > 0)
                    <ul class="list-group" id="skillList">
                        @foreach ($members as $member)
                            <div class="list-group-item">
                                <div class="media mt-0">
                                    <img src="{{ config('services.eve.urls.img') }}/{{ ucfirst($member->member_type) }}/{{ $member->member_id }}_64.{{ in_array($member->member_type, ['corporation', 'alliance']) ? "png" : "jpg" }}" class="rounded img-fluid mr-3" />
                                    <div class="media-body align-center">
                                        <h4>
                                            {{ $member->info->name }}
                                        </h4>
                                        <span class="badge badge-pill badge-secondary">{{ ucfirst($member->role) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </ul>
                @endif

            </div>
        </div>
    </div>
@endsection
