@extends('layout.index')

@section('title', $skillplan->name. " Skillplan")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 mt-3">
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <h3 class="text-center">{{ $skillplan->name }} SkillList</h3>
                @include('extra.alert')
                <hr />

                <form action="{{ route('skillplan.view', ['skillplan' => $skillplan->id]) }}" method="post">
                    <ul class="list-group">
                        @foreach ($skillplan->skillz as $skill)
                            <li class="list-group-item">
                                <div class="media">
                                    <h4 class="mr-4 mt-3">{{ $skill->pivot->position }}</h4>
                                    <div class="media-body">
                                        <h4>{{ $skill->name }} {{ $skill->pivot->level }}</h4>
                                        <p>
                                            {{ $skill->description }}
                                        </p>
                                    </div>

                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="form-group">
                        <label for="addSkill">Start Typing Skill to Add:</label>
                        <input type="text" name="addSkill" id="addSkill" class="form-control" value="{{ old('addSkill') }}" placeholder="Skill Name" />
                    </div>
                    <div class="form-group">
                        {{ csrf_field() }}
                        <button type="submit" name="action" value="addSkill" class="btn btn-primary">Submit Skill</button>
                    </div>
                </form>

            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        {{ $skillplan->name }} Settings
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>

    </script>
@endsection
