@extends('layout.index')

@section('title', $member->info->name . " Clones")

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h3 class="text-center mt-3">{{ $member->info->name }}'s Jump Clones</h3>
                <hr />
                @include('extra.alert')
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                @include('portal.extra.character')
            </div>
            <div class="col-lg-9">
                <h5>
                    My Jump Clones
                </h5>
                <hr />
                <div class="accordian" id="cloneAccordian">
                    @forelse ($member->clones as $clone)
                        <div class="card">
                            <div class="card-header" data-toggle="collapse" data-target="#{{ $clone->clone_id }}_details">
                                Clone {{ $clone->clone_id }} - {{ !is_null($clone->location) ? $clone->location->name : "Unknown Location ". $clone->location_id }}
                            </div>
                            <div id="{{ $clone->clone_id }}_details" class="collapse" data-parent="#cloneAccordian">
                                <div class="card-body p-0">
                                    <table class="table table-bordered m-0">
                                        <th>
                                            Slot
                                        </th>
                                        <th>
                                            Implant Details
                                        </th>
                                        @foreach ($clone->implants as $implant)
                                            <tr>
                                                <td class="text-center align-middle">
                                                    {{ $implant->implantAttributes->where('attribute_id', config('services.eve.dogma.attributes.implants.slot'))->first()->value }}
                                                </td>
                                                <td>
                                                    {{ $implant->name }}
                                                    <hr />
                                                    @foreach($implant->implantAttributes->whereIn('attribute_id', config('services.eve.dogma.attributes.implants.attributeModifiers')) as $modifier)
                                                        @if ($modifier->value > 0)
                                                            {{ collect(config('services.eve.dogma.attributes.implants.dictionary'))->get($modifier->attribute_id) . ": ". number_format($modifier->value, 2) }}
                                                        @endif
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty

                    @endforelse
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
