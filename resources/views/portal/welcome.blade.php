@extends('layout.index')

@section('title', 'Welcome To ESIKnife')

@section('content')
    <div class="container">
        <div class="row mt-3">
            <div class="col-lg-3">
                <img src="{{ config('services.eve.urls.img') }}/Character/{{ Auth::user()->id }}_512.jpg" class="img-fluid rounded mx-auto d-block" />
            </div>
            <div class="col-lg-9">
                <h3>Welcome to {{ config('app.name') }} {{ Auth::user()->info->name }}</h3>
                <hr />
                <p>
                    {{ config('app.name') }} is a clone, fitting, and skill management application.
                </p>
                <h3 class="mb-1">Select the Scopes!</h3>
                <hr />
                @include('extra.alert')
                <form action="{{ route('welcome') }}" method="post">
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div class="float-right">
                                        <a href="#" id="all">[Select All]</a>
                                    </div>
                                    <strong>Character Information</strong>
                                </li>
                                <label for="readCharacterClones" class="mb-0">
                                    <li class="list-group-item py-auto">
                                        <input type="checkbox" id="readCharacterClones" name="scopes[readCharacterClones]" class="item" /> <span class="ml-2">Read Character Clones</span>
                                    </li>
                                </label>

                                <label for="readCharacterImplants" class="mb-0">
                                    <li class="list-group-item py-auto">
                                        <input type="checkbox" id="readCharacterImplants" name="scopes[readCharacterImplants]" class="item" /> <span class="ml-2">Read Character Implants</span>
                                    </li>
                                </label>

                                <label for="readCharacterSkills" class="mb-0">
                                    <li class="list-group-item py-auto">
                                        <input type="checkbox" id="readCharacterSkills" name="scopes[readCharacterSkills]" class="item" /> <span class="ml-2">Read Character Skills</span>
                                    </li>
                                </label>

                                <label for="readCharacterSkillQueue" class="mb-0">
                                    <li class="list-group-item py-auto">
                                        <input type="checkbox" id="readCharacterSkillQueue" name="scopes[readCharacterSkillQueue]" class="item" /> <span class="ml-2">Read Character Skill Queue</span>
                                    </li>
                                </label>

                                <label for="readUniverseStructures" class="mb-0">
                                    <li class="list-group-item py-auto">
                                        <input type="checkbox" id="readUniverseStructures" name="scopes[readUniverseStructures]" class="item" /> <span class="ml-2">Read Structure Names</span>
                                    </li>
                                </label>
                            </ul>
                        </div>

                    </div>
                    <hr />
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-primary btn-lg">Authorize Selected Scopes</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('#all').on('click', function(){
            $(':checkbox.item').prop('checked', true);
        });
    </script>
@endsection
