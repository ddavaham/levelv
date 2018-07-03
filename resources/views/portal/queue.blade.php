@extends('layout.index')

@section('title', Auth::user()->info->name . " Dashboard")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mt-3">{{ $member->info->name }}'s Skill Queue</h3>
                <hr />
                @include('extra.alert')
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('portal.extra.portal')
            </div>
            <div class="col-lg-9">
                <h5>
                    My Skill Queue
                </h5>
                <hr />
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header text-center">
                                # of Skills Training
                            </div>
                            <div class="card-body text-center">
                                <h3>{{ $member->queue->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header text-center">
                                Total Skillpoints Training
                            </div>
                            <div class="card-body text-center">
                                <h3>{{ number_format($spTraining, 0) }} sp</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header text-center">
                                Queue Completion Date
                            </div>
                            <div class="card-body text-center">
                                <h5>{{ $queueComplete }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body p-0">
                        <table class="table table-bordered m-0"?>
                            <tr>
                                <td colspan="4">
                                    <div class="float-right">
                                        <small>Time displayed is the amount of time from right now to complete the skill. Not the amount of time it will take to train the skill</small>
                                    </div>
                                </td>
                            </tr>
                            @foreach ($member->queue as $skill)
                                <tr>
                                    <td class="text-white" rowspan="2">
                                        {{ $skill->pivot->queue_position }}
                                    </td>
                                    <td rowspan="2">
                                        {{ $skill->group->name }}
                                    </td>
                                    <td>
                                        {{ $skill->name }} {{ num2rom($skill->pivot->finished_level) }} (Training {{ number_format($skill->pivot->level_end_sp - $skill->pivot->training_start_sp) }} sp) <br /> Training Complete on: <strong>{{ \Carbon\Carbon::parse($skill->pivot->finish_date)->toDayDateTimeString() }}</strong>
                                    </td>
                                    <td class="align-middle">
                                        <div class="float-right">{{ age(now(), Carbon::parse($skill->pivot->finish_date)) }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        {{ $skill->description }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ url('js/jcountdown.min.js') }}"></script>
    <script>
        $('[data-countdown]').each(function() {
            var $this = $(this), finalDate = $(this).data('countdown');
            $this.countdown(finalDate, function(event) {
                $this.html(event.strftime("%D dys %H:%M:%S remaining"));
            });
        });

    </script>
@endsection
