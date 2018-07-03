<?php

namespace LevelV\Http\Controllers;

use Auth,Carbon, Request, Session, Validator;
use LevelV\Models\Skillplan;
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
                $plan = Skillplan::find($id);
                if (!is_null($plan)) {
                    $id = hash('md5', str_slug($name, '-').str_random(16));
                } else {
                    break;
                }
            }
            $create = Skillplan::create([
                'id' => $id,
                'name' => $name,
                'author_id' => Auth::user()->id
            ]);
            Session::flash('alert', [
                'header' => "Skillplan {$name} Created Successfully",
                'message' => "Your skillplan <strong>{$name}</strong> has been created successfully and is ready to start having skillz added to it.",
                'type' => 'info',
                'close' => 1
            ]);
            return redirect(route('skillplan.view', ['skillplan' => $create->id]));
        }
        $skillplans = SkillPlan::with('skillz')->get();
        return view('portal.skillplans.list')->withSkillplans($skillplans);
    }

    public function view(Skillplan $skillplan)
    {
        if (Request::isMethod('post')) {
            ini_set('max_execution_time', 180);
            $validator = Validator::make(Request::all(), [
                'addSkill' => "sometimes|nullable|min:1|max:255",
                'action' => "required"
            ]);
            if ($validator->failed()) {
                return redirect(route('skillplans.view', ['skillplan' => $skillplan->id]))->withErrors($validator)->withInput();
            }
            if (Request::has('action')) {
                $action = Request::get('action');
                if (Request::has('addSkill')) {
                    $skill = Request::get('addSkill');
                    $attributeRank = config('services.eve.dogma.attributes.skillz.rank');
                    $skillType = Type::where('name', $skill)->with('skillAttributes')->first();
                    $skillAttributes = $skillType->skillAttributes->keyBy('attribute_id');
                    $skillRank = (int)$skillAttributes->get($attributeRank)->value;
                    if (is_null($skillType)) {
                        Session::flash('alert', [
                            'header' => "Unknown Skill",
                            'message' => "The skill <strong>". $skill ."</strong> is not a known skill to this application. Please try again. If error persists, please submit bug report",
                            'type' => 'info',
                            'close' => 1
                        ]);
                        return redirect(route('skillplan.view', ['skillplan' => $skillplan->id]))->withInput();
                    }
                    $skillplan->load('skillz');

                    $skillOnPlan = $skillplan->skillz->where('id', $skillType->id);
                    $skillCount = $skillplan->skillz->count() ? $skillplan->skillz->count() : 1;
                    $skillsToAttach = collect();
                    if ($skillOnPlan->isNotEmpty()) {
                        $highest = $skillOnPlan->last()->pivot->level;
                        if ($highest == 5) {
                            Session::flash('alert', [
                                'header' => "Skill Maxed Out",
                                'message' => "The skill <strong>". $skill ."</strong> is already on this skill plan at its maxed level. Please try again. If error persists, please submit bug report",
                                'type' => 'info',
                                'close' => 1
                            ]);
                            return redirect(route('skillplan.view', ['skillplan' => $skillplan->id]))->withInput();
                        }
                        $skillsToAttach->push(collect([
                            'type_id' => $skillType->id,
                            'level' => $highest + 1,
                            'position' => $skillCount + 1
                        ]));
                    } else {
                        $total = $skillCount;
                        $skillsToAttach = collect();
                        if (!$skillplan->ignore_preq) {
                            $skillType->load('skillAttributes');
                            $skillsToAttach = $skillsToAttach->merge($this->collectSkillRequirements($skillType));
                        }
                        $skillsToAttach->push(collect([
                            'type_id' => $skillType->id,
                            'level' => 1
                       ]));

                    }
                    $skillsToAttach->each(function ($skillToAttach, $key) use ($skillsToAttach) {
                        $exists = $skillsToAttach->where('type_id', $skillToAttach->get('type_id'))->where('level', $skillToAttach->get('level'));
                        if ($exists->count() > 1) {
                            $existKeys = $exists->keys();
                            foreach ($exists as $k => $x) {
                                if ($k !== $existKeys->first() && $skillsToAttach->has($k)) {
                                    $skillsToAttach->forget($key);
                                }
                            }
                        }
                    });
                    $skillPlanSkillz = $skillplan->skillz;
                    $skillsToAttach->each(function ($details, $key) use ($skillPlanSkillz, $skillsToAttach) {
                        $hasSkill = $skillPlanSkillz->where('id', $details->get('type_id'));
                        if ($hasSkill->isNotEmpty()) {
                            $hasLevel = $hasSkill->where('pivot.level', $details->get('level'));
                            if ($hasLevel->isNotEmpty()) {
                                $skillsToAttach->forget($key);
                            }
                        }
                    });
                    foreach($skillsToAttach as $requiredSkill) {
                        $requiredSkill->put('position', $total);
                        $total = $total + 1;
                    }
                    $skillplan->skillz()->attach($skillsToAttach->toArray());
                    return redirect(route('skillplan.view', ['skillplan' => $skillplan->id]));
                }
            }
        }

        return view('portal.skillplans.view')->withSkillplan($skillplan);
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
            $rank = (int)$skillAttributes->get($rankKey)->value;
            foreach($skillMap as $skillId => $skillLvl){
                if ($skillAttributes->has($skillId) && $skillAttributes->has($skillLvl)) {
                    $id = (int)$skillAttributes->get($skillId)->value;
                    $lvl = (int)$skillAttributes->get($skillLvl)->value;
                    $skill = $results->where('type_id', $id);
                    if ($skill->isNotEmpty()) {
                        $level = $skill->sortByDesc('level')->first();
                        if ($level->get('level') < $lvl) {
                            for($x=$skill->get('level'); $x<$lvl;$x++) {
                                $results->prepend(collect([
                                    'level' => $x,
                                    'type_id' => $id
                                ]));
                            }
                        }
                    } else {
                        for($x=$lvl; $x>=1;$x--) {
                            $results->prepend(collect([
                                'level' => $x,
                                'type_id' => $id,
                            ]));
                        }
                    }
                    $requirementSkillType = $this->dataCont->getType($id);
                    unset($id, $lvl, $skill);
                    $status = $requirementSkillType->get('status');
                    $payload = $requirementSkillType->get('payload');
                    if ($status) {
                        $requirementSkillType = $payload->load('skillAttributes');
                        $results = $this->collectSkillRequirements($requirementSkillType, $results);
                    }
                }
            }
        }
        return $results;
    }
}
