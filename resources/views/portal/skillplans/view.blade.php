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
            <div class="col-md-8">
                <h3 class="text-center">
                    <div class="float-right">
                        <a href="#" data-toggle="collapse" data-target="#addSkillCollapse">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                    {{ $plan->name }} SkillList
                </h3>

                <hr />
                <div class="collapse" id="addSkillCollapse">
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
                        <div class="form-group">
                            <label for="addSkill">Start Typing Skill to Add:</label>
                            <input type="text" name="skillToAdd" id="skillToAdd" class="form-control" value="{{ old('addSkill') }}" placeholder="Skill Name" />
                        </div>
                        <div class="form-group">
                            {{ csrf_field() }}
                            <button type="submit" name="action" value="addSkill" class="btn btn-sm btn-primary">Submit Skill</button>
                        </div>
                        <hr />
                    </form>
                </div>
                @include('extra.alert')
                <ul class="list-group sortable" id="skillList">
                    @foreach ($plan->skillz as $key=>$skill)
                        <li class="list-group-item" id="{{ $key }}">
                            <div class="float-right">
                                <form action="{{ route('skillplan.view', ['skillplan' => $plan->id, 'delete' => $key]) }}" method="post">
                                    {{ csrf_field() }}
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                            <span data-skill="{{ $skill->type_id }}" data-level="{{ $skill->level }}">{{ $skill->info->name }} {{ num2rom($skill->level) }} (x{{ $skill->rank }})</span><br />
                            <small>Primary Attribute: {{ ucfirst(collect(config('services.eve.dogma.attributes.map'))->get((int)$skill->info->skillAttributes->keyBy('attribute_id')->get(config('services.eve.dogma.attributes.skillz.primary'))->value)) }} / Secondary Attribute: {{ ucfirst(collect(config('services.eve.dogma.attributes.map'))->get((int)$skill->info->skillAttributes->keyBy('attribute_id')->get(config('services.eve.dogma.attributes.skillz.secondary'))->value)) }}</small>
                        </li>
                    @endforeach
                </ul>
                <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" id="submittedList" name="submittedList" value="{{ $plan->skillz->keys()->implode(",") }}" />
                    <button type="submit" name="action" value="save" class="btn btn-primary mt-2">Save Plan</button>
                </form>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header" data-toggle="collapse" data-target="#settingsBody">
                        {{ $plan->name }} Settings <small>Click to Collapse</small>
                    </div>
                    <div class="list-group collapse show" id="settingsBody">
                        <div class="list-group-item">
                            <div class="float-right">
                                {{ $plan->skillz->count() }}
                            </div>
                            Total Number of Skills on Plan
                        </div>
                        <div class="list-group-item">
                            <div class="float-right">
                                {{ $plan->total_sp }}
                            </div>
                            Total SP In Skillplan
                        </div>
                        <div class="list-group-item">
                            <div class="float-right">
                                {{ $plan->training_time }}
                            </div>
                            Calculated Training Time
                        </div>
                        <div class="list-group-item">
                            <div class="float-right">
                                {{ $plan->is_public ? "Yes" : "No" }}
                            </div>
                            Plan is Public
                        </div>
                        <div class="list-group-item">
                            <div class="float-right">
                                @if ($attributeComp->isNotEmpty()) {{ ucfirst($attributeComp->keys()->get(0)) }} ({{ $attributeComp->get($attributeComp->keys()->get(0)) }}) @else {{ "N/A" }} @endif
                            </div>
                            Primary Attribute
                        </div>
                        <div class="list-group-item">
                            <div class="float-right">
                                @if ($attributeComp->isNotEmpty()) {{ ucfirst($attributeComp->keys()->get(1)) }} ({{ $attributeComp->get($attributeComp->keys()->get(1)) }}) @else {{ "N/A" }} @endif
                            </div>
                            Secondary Attribute
                        </div>
                        <div class="list-group-item">
                            <div class="btn-group d-flex">
                                <button type="button" class="btn btn-secondary w-100 dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Action Menu
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    @if($plan->is_public)
                                        <button type="button" data-toggle="modal" data-target="#makePlanPrivate" class="dropdown-item">Make Plan Private</button>
                                    @else
                                        <button type="button" data-toggle="modal" data-target="#makePlanPublic" class="dropdown-item">Make Plan Public</button>
                                    @endif
                                    <button type="button" data-toggle="modal" data-target="#deleteSkillz" class="dropdown-item">Delete All Skillz</button>
                                    <button type="button" data-toggle="modal" data-target="#deletePlan" class="dropdown-item">Delete Plan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
                    <div class="card">
                        <div class="card-header" data-toggle="collapse" data-target="#attributesBody">
                            {{ $plan->name }} Attributes <small>Click to Collapse</small>
                        </div>
                        <div class="collapse" id="attributesBody">
                            <div class="card-body p-0">
                                <table class="table table-bordered m-0">
                                    @foreach ($plan->attributes as $attribute => $value)
                                        <tr>
                                            <td>
                                                {{ ucfirst($attribute) }}
                                            </td>
                                            <td width=35%>
                                                <input type="number" name="attributes[{{ $attribute }}]" value="{{ $value }}" min="0" class="form-control form-control-sm"/>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="card-footer">
                                {{ csrf_field() }}
                                <button type="submit" name="action" value="updateAttributes" class="btn btn-primary btn-block">Update Attributes</button>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="card">
                    <div class="card-header" data-toggle="collapse" data-target="#planRemaps">
                        {{ $plan->name }} Remaps <small>Click to Collapse</small>
                    </div>
                    <div class="collapse" id="planRemaps">
                        <div class="card-body p-0">
                            <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
                                <table class="table table-bordered m-0">
                                    @foreach ($plan->remaps as $key => $value)
                                        <tr>
                                            <td colspan="2">
                                                <a href="#" data-toggle="modal" data-target="#remap_{{ $key }}">{{ $key + 1 }}. After {{ $plan->skillz->get($key)->info->name }} Level {{ $plan->skillz->get($key)->level }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="2">
                                            <select name="afterPosition" class="form-control form-control-sm">
                                                <option value="">-- Select A Skill -- </option>
                                                @foreach ($plan->skillz as $skill)
                                                    <option value="{{ $skill->position }}">{{ $skill->position + 1 }}. After {{ $skill->info->name }} Level {{ $skill->level }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    @foreach ($plan->attributes as $attribute => $value)
                                        <tr>
                                            <td>
                                                {{ ucfirst($attribute) }}
                                            </td>
                                            <td width=35%>
                                                <input type="number" name="remappedAttr[{{ $attribute }}]" value="{{ $value }}" min="0" class="form-control form-control-sm"/>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="2">
                                            {{ csrf_field() }}
                                            <button type="submit" name="action" value="addRemap" class="btn btn-block btn-primary">Add Remap</button>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="invalidAction" tabindex="-1" role="dialog" aria-labelledby="invalidActionLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="invalidActionLabel">It doesn't work like that</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div style="width:100%;height:0;padding-bottom:56%;position:relative;">
                        <iframe src="https://giphy.com/embed/OMZRxGyZZ6fGo" width="100%" height="100%" style="position:absolute" frameBorder="0" class="giphy-embed"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="deleteSkillz" tabindex="-1" role="dialog" aria-labelledby="invalidActionLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePlanLabel">Action Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>
                        Are you sure you want to delete all the skillz from this skill plan? This action cannot be reversed!
                    </p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan]) }}" method="post">
                        {{ method_field('delete') }}
                        {{ csrf_field() }}
                        <div class="float-left">
                            <button type="submit" name="target" value="skillz" class="btn btn-danger">Yes, Nuke The Skillz</button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Whoops!!! Close This</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="deletePlan" tabindex="-1" role="dialog" aria-labelledby="deletePlanLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePlanLabel">Action Confirmation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>
                        Are you sure you want to delete the entire skill plan?
                    </p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan]) }}" method="post">
                        {{ method_field('delete') }}
                        {{ csrf_field() }}
                        <div class="float-left">
                            <button type="submit" name="target" value="skillz" class="btn btn-danger">Yes, Nuke the Plan!</button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Whoops!!! Close This</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="makePlanPublic" tabindex="-1" role="dialog" aria-labelledby="deletePlanLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePlanLabel">Going Public?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>
                        Are you sure you want to make this plan public? By doing so, it'll be listed in the main skillplan list.
                    </p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan]) }}" method="post">
                        {{ csrf_field() }}
                        <div class="float-left">
                            <button type="submit" name="action" value="makePublic" class="btn btn-primary">Yes, Make the Plan Public!</button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Whoops!!! Close This</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="makePlanPrivate" tabindex="-1" role="dialog" aria-labelledby="deletePlanLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePlanLabel">Keeping it Private?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>
                        Making this plan private will prevent anybody else from being able to see it. Are you sure?
                    </p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan]) }}" method="post">
                        {{ csrf_field() }}
                        <div class="float-left">
                            <button type="submit" name="action" value="makePrivate" class="btn btn-primary">Yes, Make the Plan Private</button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Whoops!!! Close This</button>
                </div>
            </div>
        </div>
    </div>
    @foreach ($plan->remaps as $key => $value)
        makePlanPrivate
        <div class="modal fade" id="remap_{{ $key }}" tabindex="-1" role="dialog" aria-labelledby="deletePlanLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deletePlanLabel">Remap Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        The following details a remap that occurs after {{ $plan->skillz->get($key)->info->name }} Level {{ $plan->skillz->get($key)->level }}.
                    </div>
                    <div class="modal-body p-0">
                        <table class="table table-bordered m-0">
                            @foreach ($value as $attribute => $num)
                                <tr>
                                    <td>
                                        {{ ucfirst($attribute) }}
                                    </td>
                                    <td>
                                        {{ $num }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('skillplan.view', ['skillplan' => $plan]) }}" method="post">
                            {{ csrf_field() }}
                            <div class="float-left">
                                <input type="hidden" name="deletePosition" value="{{ $key }}"  />
                                <button type="submit" name="action" value="deleteRemap" class="btn btn-danger">Delete Remap</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('js')
    <script src="{{ asset('js/sortable.min.js') }}"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
    <script>
        var skillTree={!! $tree !!};
        function validate() {
            skillList(); // Get skill list from DOM

            var validSkills={}; // build a list of valid skill levels. Prepopulate with learnt skills

            for (var i = 0; i < skillz.length;i++) {
                if (skillz[i].level > 1) {
                    if ((!(validSkills[skillz[i].skill])) || parseInt(skillz[i].level) < parseInt(validSkills[skillz[i].skill])) {
                        $('#invalidAction').modal('toggle')
                        return false; // Skill level not valid in this position
                    }
                }
                if (skillTree[skillz[i].skill]) {
                    skillValid=1; // hold state to test all requirements
                    for (var requiredSkill in skillTree[skillz[i].skill]) {
                        if (validSkills[requiredSkill]) {
                            lessthanrequired=parseInt(skillTree[skillz[i].skill][requiredSkill]) <= parseInt(validSkills[requiredSkill]);
                            if (!lessthanrequired) {
                                skillValid=0; // Doesn't meet required level
                            }
                        } else {
                            skillValid=0; // Doesn't even have the skill
                        }
                        if (!skillValid) {
                            $('#invalidAction').modal('toggle')
                            return false;
                        }

                    }
                }
                validSkills[skillz[i].skill]=skillz[i].level; // No requirements, or requirements met
            }
            return true;
        }

        function skillList()
        {
            skillz=[];
            var skillList = document.getElementById("skillList");
            var children = skillList.children;
            for (i=0; i<=children.length-1;i++) {
                skillz.push({"skill":children[i].children[1].dataset.skill, "level":children[i].children[1].dataset.level});
            }
        }


        $(document).ready(function () {
            $('.sortable').sortable({
                stop: function( event, ui ) {
                    if (!validate()) {
                        $( this ).sortable( "cancel" );
                        skillList();
                    }
                    order = $(this).sortable('toArray');
                    $('#submittedList').val(order.join(","));
                }
            });
        });
    </script>
@endsection
