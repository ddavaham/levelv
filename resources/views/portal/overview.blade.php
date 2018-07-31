@extends('layout.index')

@section('title', $member->info->name . " Dashboard")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mt-3">{{ $member->info->name }}'s Overview</h3>
                <hr />
                @include('extra.alert')
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <img src="{{ config('services.eve.urls.img') }}/Character/{{ $member->id }}_512.jpg" class="img-fluid rounded mx-auto d-block" />
            </div>
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-6">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Corporation:</strong> {{ $member->info->corporation->name }}</li>
                            @if ($member->info->alliance_id !== null)
                                <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Alliance:</strong> {{ $member->info->alliance->name }}</li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Next Skill:</strong> {{ $nextSkillComplete->name }}</li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Completed At:</strong> {{ !is_null($nextSkillComplete->pivot->finish_date) ? $nextSkillComplete->pivot->finish_date->toDateTimeString() : "N/A" }}</li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Total SP:</strong> {{ number_format($member->total_sp, 0) }} sp</li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Gender:</strong> {{ title_case($member->info->gender) }}</li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Race:</strong> {{ title_case($member->info->race->name) }}</li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Birthday:</strong> {{ $member->info->birthday->toDateString() }}</li>
                            <li class="list-group-item d-flex justify-content-between align-items-center"><strong>Age:</strong> {{ age($member->info->birthday, now()) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-3">
                @include('portal.extra.character')
            </div>
            <div class="col-lg-9">
                <h5>
                    <div class="float-right text-white">
                        <a href="#" data-toggle="collapse" data-target=".skillGroup">Expand Groups</a>
                    </div>
                    My Skills
                    <hr />
                </h5>
                <div id="skillAccordian">

                    @foreach ($skillList as $skillGroup)
                        <div class="card">
                            <div class="card-header" id="{{ $skillGroup->get('key') }}" data-toggle="collapse" data-target="#{{ $skillGroup->get('key') }}_skillz">
                                <div class="float-right">
                                    Count: {{ $skillGroup->get('count') }} / Total SP: {{ number_format($skillGroup->get('total_sp'), 0) }}
                                </div>
                                {{ $skillGroup->get('name') }}
                            </div>
                            <div id="{{ $skillGroup->get('key') }}_skillz" class="collapse skillGroup">
                                <div class="card-body p-0">
                                    <table class="table table-bordered m-0">
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            Active Skill Level
                                        </th>
                                        <th>
                                            Trained Skill Level
                                        </th>
                                        <th>
                                            Skillpoints in Skill
                                        </th>
                                        @foreach ($skillGroup->get('skillz') as $skill)
                                            <tr>
                                                <td width=35%>
                                                    @if($skill->pivot->trained_skill_level < 5)
                                                        <div class="float-right">
                                                            <form action="{{ route('overview', ['member' => $member->id]) }}" method="post">
                                                                {{ csrf_field() }}
                                                                <input type="hidden" name="id" value="{{ $skill->id }}" />
                                                                <input type="hidden" name="level" value="{{ (int) $skill->pivot->trained_skill_level + 1 }}" />
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-secondary dropdown-toggle-no-caret" data-toggle="dropdown">
                                                                        <i class="fas fa-plus"></i>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <button class="dropdown-item disabled" type="button">Select a Skillplan Below</button>
                                                                        @foreach (Auth::user()->plans as $plan)
                                                                            <button type="submit" name="addSkillToPlan" value="{{ $plan->id }}" class="dropdown-item">{{ $plan->name }}</button>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    @endif
                                                    {{ $skill->name }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $skill->pivot->active_skill_level }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $skill->pivot->trained_skill_level }}
                                                </td>
                                                <td class="text-center">
                                                    {{ number_format($skill->pivot->skillpoints_in_skill, 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
