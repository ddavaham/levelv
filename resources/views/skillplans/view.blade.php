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
                    @if (Auth::user()->id == $plan->author_id)
                        <div class="float-right">
                            <a href="#" data-toggle="collapse" data-target="#addSkillCollapse">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    @endif
                    {{ $plan->name }} SkillList
                </h3>
                <hr />
                @if (Auth::user()->id == $plan->author_id)
                    <div class="collapse {{ $plan->skillz->count() == 0 ? "show" : "" }}" id="addSkillCollapse">
                        <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
                            <div class="row">
                                <div class="form-group col-md-9">
                                    <label for="addSkill">Type Name of Any Item To Skillz:</label>
                                    <input type="text" name="skillToAdd" id="skillToAdd" class="form-control" value="{{ old('addSkill') }}" placeholder="Skill Name" />
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="skillToAddLevel">Skill Level:</label>
                                    <select name="skillToAddLevel" id="skillToAddLevel" class="form-control ml-0">
                                        @for($x=1;$x<=5;$x++)
                                            <option value="{{ $x }}">Level {{ num2rom($x) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-0 mt-0">
                                        <label for="allSkillzV">
                                            <input type="checkbox" name="allSkillzV" id="allSkillzV" /> All Prereqs to Level V
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        {{ csrf_field() }}
                                        <button type="submit" name="action" value="addSkill" class="btn btn-sm btn-primary">Submit Skill</button>
                                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="collapse" data-target="#importSkillListCollapse">Import SkillList</button>
                                    </div>
                                </div>
                            </div>
                            <hr />
                        </form>
                        <div class="collapse" id="importSkillListCollapse">
                            <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="skillToImport">Paste Skill List Below:</label>
                                        <a tabindex="0" class="format-popover" data-toggle="popover" data-trigger="focus" title="Example Loki Skillplan" data-content="{{ view('extra.skillplans.example') }}">[Format Example]</a>
                                        <textarea type="text" name="skillToImport" id="skillToImport" class="form-control" rows="20" >{{ old('skillList') }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            {{ csrf_field() }}
                                            <button type="submit" name="action" value="importSkillList" class="btn btn-sm btn-primary">Import Plan</button>
                                        </div>
                                    </div>
                                </div>
                                <hr />
                            </form>

                        </div>
                    </div>
                @endif
                @include('extra.alert')
                @if ($plan->skillz->count() > 0)
                    <ul class="list-group sortable" id="skillList">
                        @foreach ($plan->skillz as $key=>$skill)
                            <li class="list-group-item" id="{{ $key }}">
                                <div class="float-right mt-2">
                                    <form action="{{ route('skillplan.view', ['skillplan' => $plan->id, 'delete' => $key]) }}" method="post">
                                        {{ csrf_field() }}
                                        @if ($skill->trained == 2)
                                            <button type="button" class="btn btn-sm btn-success disabled" title="Skill Meets Skillplan Requirements">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @elseif ($skill->trained == 1)
                                            <button type="button" class="btn btn-sm btn-warning disabled" title="Skill Is Injected, But does not meet this level">
                                                <i class="fas fa-exclamation-circle"></i>
                                            </button>
                                        @elseif ($skill->trained == 0)
                                            <button type="button" class="btn btn-sm btn-danger disabled" title="Skill Is Not Injected.">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        @endif

                                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                                <span data-skill="{{ $skill->type_id }}" data-level="{{ $skill->level }}">{{ $skill->info->name }} {{ $skill->rom }} (x{{ $skill->info->rank }})</span><br />
                                <small>Primary Attribute: {{ $skill->info->primary }} / Secondary Attribute: {{ $skill->info->secondary }}</small>
                            </li>
                        @endforeach
                    </ul>
                    @if (Auth::user()->id == $plan->author_id)
                        <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" id="submittedList" name="submittedList" value="{{ $plan->skillz->keys()->implode(",") }}" />
                            <button type="submit" name="action" value="save" class="btn btn-primary mt-2">Save Plan</button>
                        </form>
                    @endif
                @else
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="alert alert-success">
                                <h4>Congratulations!</h4>
                                <p>
                                    You have completed all of the skillz on this skillplan!
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Author
                    </div>
                    <div class="list-group">
                        <div class="list-group-item">
                            <div class="media mt-0">
                                <img src="{{ config('services.eve.urls.img') }}/Character/{{ $plan->author->id }}_64.jpg" class="rounded img-fluid mr-3" />
                                <div class="media-body align-center">
                                    <h5>
                                        {{ $plan->author->name }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" data-toggle="collapse" data-target="#legend">
                        Legend <small>Click to Collapse</small>
                    </div>
                    <div class="list-group collapse show" id="legend">
                        <div class="list-group-item">
                            <button type="button" class="btn btn-sm btn-success disabled mr-2" title="Skill Meets Skillplan Requirements">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            Skill Injected and Trained
                        </div>
                        <div class="list-group-item">
                            <button type="button" class="btn btn-sm btn-warning disabled mr-2" title="Skill Is Injected, But does not meet this level">
                                <i class="fas fa-exclamation-circle"></i>
                            </button>
                            Skill Injected but not trained
                        </div>
                        <div class="list-group-item">
                            <button type="button" class="btn btn-sm btn-danger disabled mr-2" title="Skill Is Not Injected.">
                                <i class="fas fa-times-circle"></i>
                            </button>
                            Skill is not injected
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" data-toggle="collapse" data-target="#detailsBody">
                        {{ $plan->name }} Details <small>Click to Collapse</small>
                    </div>
                    <div class="list-group collapse show" id="detailsBody">
                        <div class="list-group-item">
                            <div class="float-right">
                                {{ $plan->skillz->count() }}
                            </div>
                            Total Number of Skills on Plan
                        </div>
                        <div class="list-group-item">
                            <div class="float-right">
                                {{ $details->get('total_sp') }}
                            </div>
                            Total SP In Skillplan
                        </div>
                        <div class="list-group-item">
                            <div class="float-right">
                                {{ $details->get('training_time') }}
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
                    </div>
                </div>
                @if ($plan->isPrivate())
                    <div class="card">
                        <div class="card-header" data-toggle="collapse" data-target="#memberListBody">
                            {{ $plan->name }} Admins & Operators <small>Click to Collapse</small>
                        </div>
                        <div class="collapse" id="memberListBody">
                            <div class="list-group">
                                <?php $admin = $plan->members->where("role", "administrator")->first(); ?>
                                <div class="list-group-item">
                                    <div class="media mt-0">
                                        <img src="{{ config('services.eve.urls.img') }}/Character/{{ $admin->member_id }}_64.jpg" class="rounded img-fluid mr-3" />
                                        <div class="media-body align-center">
                                            <h5>
                                                {{ $admin->info->name }}
                                            </h5>
                                            <span class="badge badge-pill badge-secondary">{{ ucfirst($admin->role) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($operators->where('member_id', Auth::user()->id)->first() !== null)
                                <div class="card-footer">
                                    <a href="{{ route('skillplan.members', ['skillplan' => $plan->id]) }}" class="btn btn-primary btn-block">Members List</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header" data-toggle="collapse" data-target="#optionsBody">
                        {{ $plan->name }} Options <small>Click to Collapse</small>
                    </div>
                    <div class="list-group collapse" id="optionsBody">
                        @if (Request::has('hideCompletedSkillz'))
                            <a href="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" class="list-group-item list-group-item-action">Show Completed Skillz</a>
                        @else
                            <a href="{{ route('skillplan.view', ['skillplan' => $plan->id, 'hideCompletedSkillz' => 1]) }}" class="list-group-item list-group-item-action">Hide Completed Skillz</a>
                        @endif
                        @if (Auth::user()->id == $plan->author_id)
                            @if($plan->is_public)
                                <a href="#" data-toggle="modal" data-target="#makePlanPrivate" class="list-group-item list-group-item-action">Make Plan Private</a>
                            @else
                                <a href="#" data-toggle="modal" data-target="#makePlanPublic" class="list-group-item list-group-item-action">Make Plan Public</a>
                            @endif
                            <a href="#" data-toggle="modal" data-target="#deleteSkillz" class="list-group-item list-group-item-action">Delete All Skillz</a>
                            <a href="#" data-toggle="modal" data-target="#deletePlan" class="list-group-item list-group-item-action">Delete Plan</a>
                        @endif
                    </div>
                </div>
                @if (Auth::user()->id == $plan->author_id)
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
                @endif
                <div class="card">
                    <div class="card-header" data-toggle="collapse" data-target="#planExport">
                        Export {{ $plan->name }} <small>Click to Collapse</small>
                    </div>
                    <div class="collapse" id="planExport">
                        <div class="list-group">
                            <div class="list-group-item" data-toggle="collapse" data-target="#missingSkillz">
                                Missing Skillz List <small>[Click to Expand]</small>
                            </div>
                            <div class="collapse" id="missingSkillz">
                                <div class="list-group-item">
                                    @foreach ($missingSkillz as $missingSkill)
                                        {{ $missingSkill->name }} {!! "<br />" !!}
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="list-group">
                            <div class="list-group-item" data-toggle="collapse" data-target="#skillPlanSkilz">
                                Skill Plan Skillz <small>[Click to Expand]</small>
                            </div>
                            <div class="collapse" id="skillPlanSkilz">
                                <div class="list-group-item">
                                    @foreach ($plan->skillz as $skill)
                                        {{ $skill->info->name }} {{ num2rom($skill->level) }} {!! "<br />" !!}
                                    @endforeach
                                </div>
                                <div class="list-group-item">
                                    <em>Highlight the list above and paste into queue</em>
                                </div>
                            </div>
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
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
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
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
                        {{ method_field('delete') }}
                        {{ csrf_field() }}
                        <div class="float-left">
                            <button type="submit" name="target" value="plan" class="btn btn-danger">Yes, Nuke the Plan!</button>
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
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
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
                    <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
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
                        <form action="{{ route('skillplan.view', ['skillplan' => $plan->id]) }}" method="post">
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
                skillz.push({"skill":parseInt(children[i].children[1].dataset.skill), "level":parseInt(children[i].children[1].dataset.level)});
            }
        }


        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
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

            $('.format-popover').popover({
              trigger: 'focus',
              html: true
            })
        });
    </script>
@endsection
