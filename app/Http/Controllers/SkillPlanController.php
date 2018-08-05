<?php

namespace LevelV\Http\Controllers;

use Auth, Cache, Carbon, DB, Request, Session, Validator;
use LevelV\Models\{Member, SkillPlan};
use LevelV\Models\ESI\Type;

use Illuminate\Support\Collection;

class SkillPlanController extends Controller
{
    public function __construct()
    {
        $this->dataCont = new DataController;
    }

    public function list()
    {
        if (Request::isMethod('post')) {
            $validator = Validator::make(Request::all(), [
                'name' => "required|min:5|max:50"
            ]);
            if ($validator->failed()) {
                return redirect(route('skillplans.list'))->withErrors($validator)->withInput();
            }
            $name = Request::get('name');
            $id = hash('md5', str_slug($name, '-').str_random(16));

            while(true){
                $plan = SkillPlan::find($id);
                if (!is_null($plan)) {
                    $id = hash('md5', str_slug($name, '-').str_random(16));
                } else {
                    break;
                }
            }
            $attributes = collect(config('services.eve.dogma.attributes.map'));
            $create = SkillPlan::create([
                'id' => $id,
                'name' => $name,
                'author_id' => Auth::user()->main,
                'attributes' => $attributes->each(function ($attribute, $key) use ($attributes) {$attributes->put($attribute, 17);$attributes->forget($key);})->toJson()
            ]);
            Session::flash('alert', [
                'header' => "Skillplan {$name} Created Successfully",
                'message' => "Your skillplan <strong>{$name}</strong> has been created successfully and is ready to start having skillz added to it.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect(route('skillplan.view', ['skillplan' => $create->id]));
        }
        $skillPlans = SkillPlan::paginate(50);
        return view('portal.skillplans.list', [
            'skillPlans' => $skillPlans
        ]);
    }

    public function view(string $skillPlan)
    {
        $skillPlan = SkillPlan::where(['id' => $skillPlan])->first();
        if (is_null($skillPlan)) {
            Session::flash('alert', [
                'header' => "Skillplan Not Found",
                'message' => "That skillplan does not exists. Please try again. If problem persists and you are sure the hash is correct, please create issue on Github.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect(route('skillplans.list'));
        }
        if ($skillPlan->isPrivate()) {
            $hasAccess = $this->userHasAccess($skillPlan, Auth::user());
            if (!$hasAccess) {
                Session::flash('alert', [
                    'header' => "Skillplan not accessible",
                    'message' => "That skillplan is not public and not of the entities that you are currently a member of have access to this list.",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect(route('skillplans.list'));
            }
        }
        if (Request::isMethod('delete')) {
            if (Request::has('target')) {
                $target = Request::get('target');
                if ($target === "skillz") {
                    $skillPlan->update([
                        'training_time' => 0,
                        'total_sp' => 0
                    ]);
                    $skillPlan->skillz()->delete();
                    Cache::forget($skillPlan->id);
                    return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                }
                if ($target === "plan") {
                    $skillPlan->delete();
                    return redirect(route('skillplans.list'));
                }
            }
        }
        if (Request::isMethod('post')) {
            $niceNames = [
                'attributes.charisma'=> "Charisma",
                'attributes.intelligence'=> "Intelligence",
                'attributes.memory'=> "Memory",
                'attributes.perception'=> "Perception",
                'attributes.willpower'=> "Willpower"
            ];
            $validActions = collect([
                'addSkill',
                'addRemap',
                'deleteRemap',
                'makePublic',
                'makePrivate',
                'save',
                'delete',
                'updateAttributes'
            ])->implode(',');
            $validator = Validator::make(Request::all(), [
                'action' => "required|in:{$validActions}",
                'attributes' => "required_if:action,updateAttributes|array",
                'attributes.*' => "required_if:action,updateAttributes|min:17|max:50|numeric",
                'remappedAttr.*' => "required_if:action,addRemap|min:17|max:50|numeric",
                'submittedList' => "required_if:action,save",
                'delete' => "required_if:action,delete|min:0|max:".$skillPlan->skillz->count(),
                'afterPosition' => "required_if:action,addRemap|numeric|min:0|max:".$skillPlan->skillz->count(),
                'deletePosition' => "required_if:action,deleteRemap|numeric|min:0|max:".$skillPlan->skillz->count()
            ], [
                'action.required' => "An action is required to process this request",
                'action.in' => "An invalid action has been submitted. Please use a valid action",
                'attributes.array' => "The Attributes are not formatted correctly. Please try again",
                'remappedAttr.*.max' => ":attribute must be less than :max. Please try again.",
                'attributes.*.min' => ":attribute must be greater than :min. Please try again."
            ]);
            $validator->setAttributeNames($niceNames);
            if ($validator->fails()) {
                return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]))->withErrors($validator)->withInput();
            }
            if (Request::has('action')) {
                $action = Request::get('action');
                if ($action === "addSkill" && Request::has('skillToAdd')) {
                    $search = $this->dataCont->getSearch('inventory_type', Request::get('skillToAdd'), true);
                    $status = $search->get('status');
                    if (!$status) {
                        Session::flash('alert', [
                            'header' => "Unable to Add Skill",
                            'message' => $status->get('payload')->get('message'),
                            'type' => 'danger',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.view', ['member' => $member->id, 'skillplan' => $skillPlan->id]));
                    }
                    $payload = $search->get('payload')->recursive()->get('response');
                    if (!$payload->has('inventory_type')) {
                        Session::flash('alert', [
                            'header' => "Unable to Add Skill",
                            'message' => "We are unable to identify a valid type for that phrase",
                            'type' => 'danger',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.view', ['member' => $member->id, 'skillplan' => $skillPlan->id]));
                    }
                    $typeId = $payload->get('inventory_type')->first();
                    $type = $this->dataCont->getType($typeId);
                    $status = $type->get('status');
                    if (!$status) {
                        Session::flash('alert', [
                            'header' => "Unable to Add Skill",
                            'message' => "There was an issue adding that item/skill to the plan. Please try again.",
                            'type' => 'danger',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.view', ['member' => $member->id, 'skillplan' => $skillPlan->id]));
                    }
                    $type = $type->get('payload');
                    $type->load('skillAttributes');
                    if ($type->category_id == 16) {
                        $addSkill = $this->addSkillToPlan($skillPlan, $type, (int)Request::get('skillToAddLevel'), (bool) Request::has('allSkillzV'));
                        if (!$addSkill->get('status')) {
                            Session::flash('alert', [
                                'header' => "Unable to Add Skill",
                                'message' => $addSkill->get('payload')->get('message'),
                                'type' => 'info',
                                'close' => 1
                            ]);
                        } else {
                            Session::flash('alert', [
                                'header' => "Successfully Added Skill",
                                'message' => "The skill ". $skillType->name. " has been successfully added to the skill plan",
                                'type' => 'success',
                                'close' => 1
                            ]);
                        }
                        return redirect(route('skillplan.view', ['member' => $member->id, 'skillplan' => $skillPlan->id]));
                    } else {
                        $skillMap = collect(config('services.eve.dogma.attributes.skillz.map'));
                        $skillAttributes = $type->skillAttributes->keyBy('attribute_id');
                        foreach($skillMap as $skillId => $skillLvl) {
                            if ($skillAttributes->has($skillId) && $skillAttributes->has($skillLvl)) {
                                $id = (int)$skillAttributes->get($skillId)->value;
                                $lvl = (int)$skillAttributes->get($skillLvl)->value;
                                $skillType = $this->dataCont->getType($id);
                                if (!$skillType->get('status')) {
                                    Session::flash('alert', [
                                        'header' => "Unable to Add Skill",
                                        'message' => $skillType->get('payload')->get('message'),
                                        'type' => 'info',
                                        'close' => 1
                                    ]);
                                }
                                $skillType =$skillType->get('payload');
                                $skillType->load('skillAttributes');
                                $addSkill = $this->addSkillToPlan($skillPlan, $skillType, (int)$lvl, (bool) Request::has('allSkillzV'));
                                if (!$addSkill->get('status')) {
                                    Session::flash('alert', [
                                        'header' => "Unable to Add Skill",
                                        'message' => $addSkill->get('message'),
                                        'type' => 'info',
                                        'close' => 1
                                    ]);
                                    break;
                                }
                            }
                        }
                        return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                    }
                }
                if ($action === "updateAttributes" && Request::has('attributes')) {
                    $skillPlan->update([
                        'attributes' => collect(Request::get('attributes'))->toJson()
                    ]);
                    $this->calculateTrainingTimeAndSP($skillPlan);
                    return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                }
                if ($action === "save" && !is_null(Request::get('submittedList'))) {
                    $newSkillz = collect();
                    $ids = collect(explode(',', Request::get('submittedList')));
                    $currentSkillz = $skillPlan->skillz;
                    $x = 0;
                    $ids->each(function ($id) use ($currentSkillz, $newSkillz, &$x) {
                        $currentSkillz->get($id)->position = $x;
                        $skill = $currentSkillz->get($id);
                        $newSkillz->push(collect([
                            'position' => $x,
                            'type_id' => $skill->type_id,
                            'level' => $skill->level,
                            'rank' => $skill->rank,
                            "primaryAttribute" => $skill->primaryAttribute,
                            "secondaryAttribute" => $skill->secondaryAttribute
                        ]));
                        $x++;
                    });
                    $skillPlanIsValid = true;
                    $skillTree = collect(json_decode(Cache::get($skillPlan->id), true))->recursive();
                    foreach ($newSkillz as $skill) {
                        if ($skillTree->has($skill->get('type_id')) && $skillPlanIsValid) {
                            $skillOnSkillTree = $skillTree->get($skill->get('type_id'));
                            foreach ($skillOnSkillTree as $key=>$value) {
                                $skillRequirementOnPlan = $newSkillz->where('type_id', $key)->sortByDesc('level')->first();
                                if ($skillRequirementOnPlan->get('level') < $value) {
                                    $skillPlanIsValid = false;
                                    break 1;
                                }
                            }
                        }
                    }
                    if (!$skillPlanIsValid) {
                        Session::flash('alert', [
                            'header' => "SkillPlan Validation Failed",
                            'message' => "Validation of the skillplan failed. Please try submitting the skill plan again",
                            'type' => 'info',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                    }
                    $skillPlan->skillz()->delete();
                    $skillPlan->skillz()->createMany($newSkillz->toArray());
                    return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                }
                if ($action === "delete") {
                    $delete = Request::get('delete');
                    $skillAtPosition = $skillPlan->skillz->get($delete);
                    $skillTree = collect(json_decode(Cache::get($skillPlan->id), true))->recursive();
                    $skillOnSkillPlan = $skillPlan->skillz->where('type_id', $skillAtPosition->type_id)->sortByDesc('level');
                    foreach ($skillOnSkillPlan as $sameSkill) {
                        if ($skillAtPosition->level < $sameSkill->level) {
                            Session::flash('alert', [
                                'header' => "Unable to remove skill",
                                'message' => $sameSkill->info->name . " requires this skill to be on the plan",
                                'type' => 'warning',
                                'close' => 1
                            ]);
                            return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                        }
                        foreach ($skillTree as $parentSkillId => $skillRequirements) {
                            if ($skillRequirements->has($skillAtPosition->type_id) && $skillRequirements->get($skillAtPosition->type_id) >= $skillAtPosition->level) {
                                $skillInfo = $skillPlan->skillz->where('type_id', $parentSkillId)->sortBy('level')->first();
                                Session::flash('alert', [
                                    'header' => "Unable to remove skill",
                                    'message' => $skillInfo->info->name . " level " . $skillInfo->level . " requires the " . $skillAtPosition->info->name . " skill to be at level " . $skillAtPosition->level . " to be on the plan.",
                                    'type' => 'info',
                                    'close' => 1
                                ]);
                                return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                            }
                        }
                    }

                    $skillPlan->skillz()->where('position', $delete)->delete();
                    $skillPlan->skillz()->where('position', '>', $delete)->decrement('position');
                    $skillPlan->load('skillz');
                    $this->updateSkillTree($skillPlan);
                    $totalSP = 0;
                    $trnTime = 0; // in Minutes
                    $planAttributes = $skillPlan->attributes;
                    foreach($skillPlan->skillz as $skill) {
                        $spInLevel = pow(2, 2.5 * ($skill->level - 1)) * 250 * $skill->rank;
                        $spPerMinute = $planAttributes->get($skill->primaryAttribute) + ($planAttributes->get($skill->secondaryAttribute)/2);
                        $trnTime += ceil($spInLevel/$spPerMinute);
                        $totalSP += $spInLevel;
                        unset($spInLevel,$spPerMinute);
                    }
                    $skillPlan->update([
                        'training_time' => $trnTime,
                        'total_sp' => $totalSP,
                    ]);
                    return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                }
                if ($action === "makePublic") {
                    $skillPlan->update([
                        'is_public' => 1
                    ]);
                    return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                }
                if ($action === "makePrivate") {
                    $hasAdmin = $skillPlan->members()->where('member_id', Auth::user()->id)->first();
                    if (is_null($hasAdmin)) {
                        $skillplan->members()->create([
                            'member_id' => Auth::user()->id,
                            'member_type' => "character",
                            'role' => "administrator"
                        ]);
                    }
                    $skillPlan->update([
                        'is_public' => 0
                    ]);
                    return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                }
                if ($action === "addRemap") {
                    $currentRemaps = $skillPlan->remaps;
                    $afterPosition = Request::get('afterPosition');
                    if ($currentRemaps->has($afterPosition)) {
                        Session::flash('alert', [
                            'header' => "Remap Already In Place",
                            'message' => "A Remap has already been setup for that position. Please select a different position",
                            'type' => 'warning',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                    }
                    $currentRemaps = $currentRemaps->put($afterPosition, collect(Request::get('remappedAttr')));
                    $skillPlan->update([
                        'remaps' => $currentRemaps->toJson()
                    ]);
                    $this->calculateTrainingTimeAndSP($skillPlan);
                    return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                }
                if ($action === "deleteRemap") {
                    $currentRemaps = $skillPlan->remaps;
                    $deletePosition = Request::get('deletePosition');
                    if (!$currentRemaps->has($deletePosition)) {
                        Session::flash('alert', [
                            'header' => "Remap Not Set",
                            'message' => "There is not a remap at the position that you specified",
                            'type' => 'warning',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                    }
                    $currentRemaps->forget($deletePosition);
                    $skillPlan->update([
                        'remaps' => $currentRemaps->toJson()
                    ]);
                    $this->calculateTrainingTimeAndSP($skillPlan);
                    return redirect(route('skillplan.view', ['skillplan' => $skillPlan->id]));
                }
            }

        }
        // Generate a SkillTree for the Javascript to use to validate positions on the plan
        $skillTree = $this->generateSkillTree($skillPlan);
        // Generate the how many skillz of each attribute on are on the skillplan
        $attributeComp = collect(config('services.eve.dogma.attributes.map'));
        $attributeComp->each(function ($attribute, $key) use ($attributeComp) {$attributeComp->put($attribute, 0);$attributeComp->forget($key);});
        $this->buildAttributeComp($skillPlan->skillz)->each(function ($value, $key) use ($attributeComp) {
            $attributeComp->put($key, $value);
        });
        $attributeComp = $attributeComp->arsort();

        // loop through each skill on the plan and set a flag to determine if the skill is injected, trained, injected but not trained, or needs to be purchased.
        // 0 = Skill Not Injected, 1 = Skill Inject, Not Trained, 2 = Skill At or Above Level
        $charSkillz = Auth::user()->skillz->keyBy('id');
        $missingSkillz = collect();

        foreach ($skillPlan->skillz as $key=>$skill) {
            if ($charSkillz->has($skill->type_id)) {
                if ($charSkillz->get($skill->type_id)->pivot->trained_skill_level < $skill->level) {
                    $skill->trained = 1;
                } else {
                    if (Request::has('showCompletedSkillz')) {
                        $skill->trained = 2;
                    } else {
                        $skillPlan->skillz->forget($key);
                    }
                }
            } else {
                $skill->trained = 0;
                if (!$missingSkillz->has($skill->type_id)) {
                    $missingSkillz->put($skill->type_id, $skill->info);
                }
            }
        }

        $totalSP = 0;
        $trnTime = 0; // in Minutes
        $planAttributes = $skillPlan->attributes->recursive();
        foreach ($skillPlan->skillz as $key=>$skill) {
            if ($skillPlan->remaps->has($skill->position - 1)) {
                $planAttributes = collect($skillPlan->remaps->get($skill->position - 1));
            }
            $spInLevel = pow(2, 2.5 * ($skill->level - 1)) * 250 * $skill->rank;
            $spPerMinute = $planAttributes->get($skill->primaryAttribute) + ($planAttributes->get($skill->secondaryAttribute)/2);
            $trnTime += ceil($spInLevel/$spPerMinute);
            $totalSP += $spInLevel;
            unset($spInLevel,$spPerMinute);
        }
        $day = floor ($trnTime / 1440); $hour = floor (($trnTime - $day * 1440) / 60); $min = $trnTime - ($day * 1440) - ($hour * 60);

        $details = collect([
            'training_time' => number_format($day, 0). "d ".number_format($hour, 0)."h ".number_format($min, 0)."m",
            'total_sp' => number_format($totalSP, 0) . " SP"
        ]);

        if ($skillPLan->isPrivate()) {
            $skillPlan->load('members');

            $operators = $skillPlan->members->whereIn('role',  ['administrator', 'operator']);
        }

        return view('skillplans.view', [
            'plan' => $skillPlan,
            'details' => $details,
            'tree' => $skillTree,
            'attributeComp' => $attributeComp,
            'missingSkillz' => $missingSkillz,
            'operators' => $skillPlan->isPrivate() ? $operators : null
        ]);
    }

    public function members(string $skillPlan)
    {
        $skillPlan = SkillPlan::findOrFail($skillPlan);
        if ($skillPlan->isPrivate()) {
            $hasAccess = $this->userHasAccess($skillPlan, Auth::user());
            if (!$hasAccess) {
                Session::flash('alert', [
                    'header' => "Skillplan not accessible",
                    'message' => "That skillplan is not public and not of the entities that you are currently a member of have access to this list.",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect(route('skillplans.list'));
            }
        }
        $members = $skillPlan->members()->paginate(25);
        if (Request::isMethod('post')) {
            $validator = Validator::make(Request::all(), [
                'entityToAdd' => "sometimes|nullable|min:3|max:100",

                // 'promoteEntity' => "required_without:entityToAdd,demoteEntity,removeEntity",
                // 'demoteEntity' => "required_without:entityToAdd,removeEntity,promoteEntity",
                // 'removeEntity' => "required_without:entityToAdd,demoteEntity,promoteEntity",
            ]);
            if ($validator->fails()) {
                return redirect(route('skillplan.members', ['skillplan' => $skillPlan->id]))->withErrors($validator)->withInput();
            }
            if (Request::has('entityToAdd') && !is_null(Request::get('entityToAdd'))) {
                $entity = Request::get('entityToAdd');
                $search = $this->dataCont->getSearch("character,corporation,alliance", $entity, true);
                // dd($search);
                $status = $search->get('status');
                $payload = collect($search->get('payload')->get('response'))->recursive();
                if (!$status || ($status && $payload->isEmpty())) {
                    Session::flash('alert', [
                        'header' => "Unknown Entity",
                        'message' => "CCP does not know who that entity is. Please try another entity.",
                        'type' => 'danger',
                        'close' => 1
                    ]);
                    return redirect(route('skillplan.members', ['skillplan' => $skillPlan->id]))->withInput();
                }
                $ids = $payload->flatten();
                $getNames = $this->dataCont->postUniverseNames($ids);
                $status = $getNames->get('status');
                $payload = collect($getNames->get('payload')->get('response'))->recursive();
                return view('skillplans.members', [
                    'plan' => $skillPlan,
                    'members' => $members,
                    'results' => $payload->recursive()
                ]);
            }
            if (Request::has('addEntity')) {
                $entity = collect(explode("::", Request::get('addEntity')));
                if ($entity->count() !== 2) {
                    Session::flash('alert', [
                        'header' => "Invalid Entity Selection",
                        'message' => "Something went wrong with your entity selection. Please try again",
                        'type' => 'danger',
                        'close' => 1
                    ]);
                    return redirect(route('skillplan.members', ['skillplan' => $skillPlan->id]));
                }
                $id = $entity->get(0);
                $type = $entity->get(1);
                if ($type === "character") {
                    $char = $this->dataCont->getCharacter($id);
                    if (!$char->get('status')) {
                        Session::flash('alert', [
                            'header' => "Unable to Add Character",
                            'message' => "We are unable to query ESI for details regarding this character. Please try again shortly.",
                            'type' => 'danger',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.members', ['skillplan' => $skillPlan->id]));
                    }

                    $skillPlan->members()->create([
                        'member_id' => $id,
                        'member_type' => $type,
                        'role' => "member"
                    ]);

                    Session::flash('alert', [
                        'header' => "Character Add To Plan Successfully",
                        'message' => "We were successfully able to add the character " . $char->get('payload')->name . " to the skillplan.",
                        'type' => 'success',
                        'close' => 1
                    ]);
                }
                if ($type === "corporation") {
                    $corp = $this->dataCont->getCorporation($id);
                    if (!$corp->get('status')) {
                        Session::flash('alert', [
                            'header' => "Unable to Add Corporation",
                            'message' => "We are unable to query ESI for details regarding this corporation. Please try again shortly.",
                            'type' => 'danger',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.members', ['skillplan' => $skillPlan->id]));
                    }

                    $skillPlan->members()->create([
                        'member_id' => $id,
                        'member_type' => $type,
                        'role' => "member"
                    ]);

                    Session::flash('alert', [
                        'header' => "Corporation Add To Plan Successfully",
                        'message' => "We were successfully able to add the corporation " . $corp->get('payload')->name . " to the skillplan.",
                        'type' => 'success',
                        'close' => 1
                    ]);
                }
                if ($type === "alliance") {
                    $alli = $this->dataCont->getAlliance($id);
                    if (!$alli->get('status')) {
                        Session::flash('alert', [
                            'header' => "Unable to Add Alliance",
                            'message' => "We are unable to query ESI for details regarding this alliance. Please try again shortly.",
                            'type' => 'danger',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.members', ['skillplan' => $skillPlan->id]));
                    }

                    $skillPlan->members()->create([
                        'member_id' => $id,
                        'member_type' => $type,
                        'role' => "member"
                    ]);

                    Session::flash('alert', [
                        'header' => "Alliance Add To Plan Successfully",
                        'message' => "We were successfully able to add the alliance " . $alli->get('payload')->name . " to the skillplan.",
                        'type' => 'success',
                        'close' => 1
                    ]);
                }
                return redirect(route('skillplan.members', ['skillplan' => $skillPlan->id]));
            }

        }

        return view('skillplans.members', [
            'plan' => $skillPlan,
            'members' => $members
        ]);
    }

    public function userHasAccess(Skillplan $skillPlan, Member $member)
    {
        $member->load('info.corporation');
        $validIds = collect();
        if ($member->info->corporation->alliance_id !== null) {
            $validIds->push($member->info->corporation->alliance_id);
        }
        $validIds->push($member->info->corporation_id);
        $validIds->push($member->id);

        $hasAccess = $skillPlan->members()->whereIn('member_id', $validIds->toArray())->get();
        if ($hasAccess->isEmpty()) {
            return false;
        }
        return true;
    }

    public function addSkillToPlan(SkillPlan $skillPlan, Type $skillType, int $level=null, bool $allSkillzV=null)
    {
        $attributeRank = config('services.eve.dogma.attributes.skillz.rank');
        $priAttrKey = config('services.eve.dogma.attributes.skillz.primary');
        $secAttrKey = config('services.eve.dogma.attributes.skillz.secondary');

        $skillPlan->load('skillz');
        $skillType->load('skillAttributes');
        $skillAttributes = $skillType->skillAttributes->keyBy('attribute_id');
        $skillRank = (int)$skillAttributes->get($attributeRank)->value;
        $skillPriAttr = collect(config('services.eve.dogma.attributes.map'))->get((int)$skillAttributes->get(config('services.eve.dogma.attributes.skillz.primary'))->value);
        $skillSecAttr = collect(config('services.eve.dogma.attributes.map'))->get((int)$skillAttributes->get(config('services.eve.dogma.attributes.skillz.secondary'))->value);

        $skillOnPlan = $skillPlan->skillz->where('type_id', $skillType->id);
        $skillsToAttach = collect();
        if ($skillOnPlan->isNotEmpty()) {
            $highest = $skillOnPlan->last()->level;
            if ($highest < 5) {
                $skillsToAttach->push(collect([
                    'type_id' => $skillType->id,
                    'level' => $highest + 1,
                    'rank' => $skillRank,
                    'primaryAttribute' => $skillPriAttr,
                    'secondaryAttribute' => $skillSecAttr
                ]));
            }
        } else {
            $skillPrereqs = $this->collectSkillRequirements($skillType);
            foreach($skillPrereqs as $prereq){
                $likeSkillz = $skillsToAttach->where('type_id', $prereq->get('type_id'));
                $lastLvl = 1;
                if ($likeSkillz->isNotEmpty()) {
                    $lastLvl = $likeSkillz->last()->get('level');
                    $lastLvl++;
                }
                $newLevel = $allSkillzV ? 5 : $prereq->get('level');
                for($x=$lastLvl;$x<=$newLevel;$x++) {
                    $skillsToAttach->push(collect([
                        'level' => $x,
                        'rank' => $prereq->get('rank'),
                        'type_id' => $prereq->get('type_id'),
                        'primaryAttribute' => $prereq->get('primaryAttribute'),
                        'secondaryAttribute' => $prereq->get('secondaryAttribute')
                    ]));
                }
            }

            for($x=1;$x<=$level;$x++) {
                $skillsToAttach->push(collect([
                    'level' => $x,
                    'rank' => $skillRank,
                    'type_id' => $skillType->id,
                    'primaryAttribute' => $skillPriAttr,
                    'secondaryAttribute' => $skillSecAttr
               ]));
            }
        }

        $skillPlanSkillz = $skillPlan->skillz;
        $skillsToAttach->each(function ($details, $key) use ($skillPlanSkillz, $skillsToAttach) {
            $hasSkill = $skillPlanSkillz->where('type_id', $details->get('type_id'));
            if ($hasSkill->isNotEmpty()) {
                $hasLevel = $hasSkill->where('level', $details->get('level'));
                if ($hasLevel->isNotEmpty()) {
                    $skillsToAttach->forget($key);
                }
            }
        });
        $total = $skillPlanSkillz->count() + 1;
        foreach($skillsToAttach as $requiredSkill) {
            $requiredSkill->put('position', $total);
            $total += 1;
        }
        $skillPlan->skillz()->createMany($skillsToAttach->toArray());
        $skillPlan->load('skillz');
        Cache::forget($skillPlan->id);
        $skillTree = $this->generateSkillTree($skillPlan);
        Cache::put($skillPlan->id, $skillTree->toJson());

        return collect([
            'status' => true,
            'skillplan' => $skillPlan
        ]);
    }

    public function buildAttributeComp(Collection $skillz)
    {
        $comp = collect();
        foreach ($skillz as $skill)
        {
            if (!$comp->has($skill->primaryAttribute)) {
                $comp->put($skill->primaryAttribute, 0);
            }
            if (!$comp->has($skill->secondaryAttribute)) {
                $comp->put($skill->secondaryAttribute, 0);
            }
            $comp->put($skill->primaryAttribute, $comp->get($skill->primaryAttribute) + 1);
            $comp->put($skill->secondaryAttribute, $comp->get($skill->secondaryAttribute) + 1);
        }
        return $comp;
    }

    public function updateSkillTree(SkillPlan $skillPlan)
    {
        $skillTree = collect();
        Cache::forget($skillPlan->id);
        foreach($skillPlan->skillz as $skill) {
            $build = $this->buildSkillTree($skill->info);
            foreach ($build as $skillId => $prereqs) {
                $skillTree = $skillTree->put($skillId, $prereqs);
            }
        }
        Cache::put($skillPlan->id, $skillTree->toJson(), now()->addHour());
        return true;
    }

    public function generateSkillTree(Skillplan $skillPlan)
    {
        $skillTree = collect();
        if (Cache::has($skillPlan->id)) {
            $skillTree = Cache::get($skillPlan->id);
        } else {
            foreach($skillPlan->skillz as $skill) {
                $build = $this->buildSkillTree($skill->info);
                foreach ($build as $skillId => $prereqs) {
                    $skillTree->put($skillId, $prereqs);
                }
            }
            Cache::put($skillPlan->id, $skillTree->toJson(), now()->addHour());
        }
        return $skillTree;
    }

    public function buildSkillTree(Type $skillType, Collection $results=null)
    {
        if (is_null($results)) {
            $results=collect();
        }
        if ($skillType->skillAttributes->isNotEmpty()) {
            $skillAttributes = $skillType->skillAttributes->keyBy('attribute_id');
            $skillMap = collect(config('services.eve.dogma.attributes.skillz.map'));
            foreach($skillMap as $skillId => $skillLvl){
                if ($skillAttributes->has($skillId) && $skillAttributes->has($skillLvl)) {
                    $id = (int)$skillAttributes->get($skillId)->value;
                    $lvl = (int)$skillAttributes->get($skillLvl)->value;
                    if ($results->has($skillType->id)) {
                        if ($results->get($skillType->id)->has($id)) {
                            if ($results->get($skillType->id)->get($id) < $lvl) {
                                $results->get($skillType->id)->put($id, $lvl);
                            }
                        } else {
                            $results->get($skillType->id)->put($id, $lvl);
                        }
                    } else {
                        $results->put($skillType->id, collect([
                            $id => $lvl
                        ]));
                    }

                    $requiredSkillType = $this->dataCont->getType($id);
                    $status = $requiredSkillType->get('status');
                    $payload = $requiredSkillType->get('payload');
                    if (!$status) {
                        return collect();
                    }
                    $requiredSkillType = $payload->load('skillAttributes');
                    $results = $this->buildSkillTree($requiredSkillType, $results);
                }
            }
        }
        return $results;
    }

    public function collectSkillRequirements(Type $skillType, Collection $results=null)
    {
        if (is_null($results)) {
            $results=collect();
        }
        if ($skillType->skillAttributes->isNotEmpty()) {
            $skillAttributes = $skillType->skillAttributes->keyBy('attribute_id');
            $skillMap = collect(config('services.eve.dogma.attributes.skillz.map'));
            $rankKey = config('services.eve.dogma.attributes.skillz.rank');
            $priAttrKey = config('services.eve.dogma.attributes.skillz.primary');
            $secAttrKey = config('services.eve.dogma.attributes.skillz.secondary');
            $rank = (int)$skillAttributes->get($rankKey)->value;
            foreach($skillMap as $skillId => $skillLvl) {
                if ($skillAttributes->has($skillId) && $skillAttributes->has($skillLvl)) {
                    $id = (int)$skillAttributes->get($skillId)->value;
                    $lvl = (int)$skillAttributes->get($skillLvl)->value;
                    $requiredSkillType = $this->dataCont->getType($id);
                    $status = $requiredSkillType->get('status');
                    $payload = $requiredSkillType->get('payload');
                    if ($status) {
                        $requiredSkillType = $payload->load('skillAttributes');
                        $requiredSkillRank = (int)$requiredSkillType->skillAttributes->where('attribute_id', $rankKey)->first()->value;
                        $requiredSkillPriAttr = collect(config('services.eve.dogma.attributes.map'))->get((int)$requiredSkillType->skillAttributes->where('attribute_id', $priAttrKey)->first()->value);
                        $requiredSkillSecAttr = collect(config('services.eve.dogma.attributes.map'))->get((int)$requiredSkillType->skillAttributes->where('attribute_id', $secAttrKey)->first()->value);
                        $results->prepend(collect([
                            'level' => $lvl,
                            'type_id' => $id,
                            'rank' => $requiredSkillRank,
                            'primaryAttribute' => $requiredSkillPriAttr,
                            'secondaryAttribute' => $requiredSkillSecAttr
                        ]));
                        unset($id, $lvl);
                        $results = $this->collectSkillRequirements($requiredSkillType, $results);
                    }
                }
            }
        }
        return $results;
    }
}
