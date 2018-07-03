@extends('layout.index')

@section('title', Auth::user()->info->name . " Dashboard")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mt-3">{{ $member->info->name }}'s Attributes & Implants</h3>
                <hr />
                @include('extra.alert')
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('portal.extra.portal')
            </div>
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-6">
                        <h5>My Attributes</h5>
                        <hr />
                        <div class="card">
                            <div class="card-body p-0">
                                <table class="table table-bordered m-0">
                                    <tr>
                                        <td>
                                            Charisma
                                        </td>
                                        <td>
                                            {{ $member->attributes->get('charisma') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Intelligence
                                        </td>
                                        <td>
                                            {{ $member->attributes->get('intelligence') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Memory
                                        </td>
                                        <td>
                                            {{ $member->attributes->get('memory') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Perception
                                        </td>
                                        <td>
                                            {{ $member->attributes->get('perception') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Willpower
                                        </td>
                                        <td>
                                            {{ $member->attributes->get('willpower') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Bonus Remaps Remaining
                                        </td>
                                        <td>
                                            {{ $member->attributes->get('bonus_remaps') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Last Remap
                                        </td>
                                        <td>
                                            {{ $member->attributes->get('last_remap_date')->diffForHumans() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Remap Cooldown Date
                                        </td>
                                        <td>
                                            {{ $member->attributes->get('accrued_remap_cooldown_date')->diffForHumans() }}
                                        </td>
                                    </tr>

                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h5>My Implants</h5>
                        <hr />

                        @foreach($member->implants as $implant)
                            <div class="card">
                                <div class="card-header" data-toggle="collapse" data-target="#{{ $implant->id }}_desc">
                                    Slot {{ number_format($implant->implantAttributes->where('attribute_id',config('services.eve.dogma.attributes.implants.slot'))->first()->value) }} - {{ $implant->name }}
                                </div>
                                <div id="{{ $implant->id }}_desc" class="collapse">
                                    <div class="card-body">
                                        {{ $implant->description }}
                                        <hr />
                                        @foreach($implant->implantAttributes->whereIn('attribute_id', config('services.eve.dogma.attributes.implants.attributeModifiers')) as $modifier)
                                            @if ($modifier->value > 0)
                                                {{ collect(config('services.eve.dogma.attributes.implants.dictionary'))->get($modifier->attribute_id) . ": ". number_format($modifier->value, 2) }}
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-1">
                            <small>Click Headers for more info</small>
                        </div>
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
