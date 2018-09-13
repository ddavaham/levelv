<?php

namespace LevelV\Http\Controllers;

use Auth, Carbon, Request, Session, Validator;
use LevelV\Models\Member;
use LevelV\Models\ESI\Type;

class PortalController extends Controller
{
    public function __construct()
    {
        $this->dataCont = new DataController;
        $this->skillCont = new SkillPlanController;
    }

    public function dashboard ()
    {
        Auth::user()->load('alts.jobs');
        $jobs = collect([
            'pending' => Auth::user()->alts->pluck('jobs')->flatten()->whereIn('status', ['queued', 'executing'])->count()
        ]);
        return view('dashboard', [
            'jobs' => $jobs
        ]);
    }

    public function attributes(int $member)
    {
        $member = Member::with('implants.implantAttributes')->findOrFail($member);
        return view('portal.attributes')->withMember($member);
    }

    public function clones(int $member)
    {
        $member = Member::findOrFail($member);
        $member->load('clones.implants.implantAttributes');
        return view('portal.clones')->withMember($member);
    }

    public function overview (int $member)
    {
        $member = Member::findOrFail($member);
        if (Request::isMethod('post')) {
            $validator = Validator::make(Request::all(), [
                'id' => 'required|numeric',
                'level' => "required|numeric|min:1|max:5",
                'addSkillToPlan' => "required"
            ]);
            if ($validator->fails()) {
                return redirect(route('overview', ['member' => $member->id]))->withErrors($validator);
            }
            $plan = Auth::user()->plans->where('id', Request::get('addSkillToPlan'))->first();
            $type = Type::find(Request::get('id'));
            if (is_null($plan) || is_null($type)) {
                Session::flash('alert', [
                   "header" => "Unable to Add Skill",
                   'message' => "An error occured while attempting to add the skill. Please try again",
                   'type' => 'info',
                   'close' => 1
                ]);
                return redirect(route('overview', ['member' => $member->id]));
            }
            $addSkill = $this->skillCont->addSkillToPlan($plan, $type, (int)Request::get('level'));
            if (!$addSkill->get('status')) {
                Session::flash('alert', [
                    'header' => "Unable to Add Skill",
                    'message' => $addSkill->get('message'),
                    'type' => 'info',
                    'close' => 1
                ]);
            } else {
                Session::flash('alert', [
                    'header' => "Successfully Added Skill",
                    'message' => "The skill ". $type->name. " has been successfully added to the skill plan ". $plan->name,
                    'type' => 'success',
                    'close' => 1
                ]);
            }
            return redirect(route('overview', ['member' => $member->id]));
        }
        $skillList = collect();
        $member->skillz->load('group')->each(function ($skill) use ($member, $skillList) {
            if (!$skillList->has($skill->group_id)) {
                $skillList->put($skill->group_id, collect([
                    'name' => $skill->group->name,
                    'key' => implode('_', explode(' ', strtolower($skill->group->name))),
                    'skillz' => $member->skillz->where('group_id', $skill->group_id),
                    'count' => $member->skillz->where('group_id', $skill->group_id)->count(),
                    'total_sp' => $member->skillz->where('group_id', $skill->group_id)->pluck('pivot.skillpoints_in_skill')->sum()
                ]));
            }
        });
        $skillList = $skillList->sortBy('name');

        $nextSkillComplete = $member->queue()->orderby('member_skill_queue.queue_position', 'asc')->first();

        return view('portal.overview', [
            'member' => $member,
            'skillList' => $skillList,
            'nextSkillComplete' => $nextSkillComplete
        ]);
    }

    public function queue(int $member)
    {
        $member = Member::with('queue.group')->findOrFail($member);
        $groupsTraining = collect();
        $spTraining = collect();

        $member->queue->each(function ($item) use ($spTraining, $groupsTraining) {
            if (!$groupsTraining->has($item->group_id)) {
                $item->training = 0;
                $groupsTraining->put($item->group_id, $item->group);
            }
            $groupsTraining->get($item->group_id)->training = $groupsTraining->get($item->group_id)->training + 1;
            if (!is_null($item->pivot->level_end_sp) && !is_null($item->pivot->training_start_sp)) {
                $spTraining->push($item->pivot->level_end_sp - $item->pivot->training_start_sp);
            }
        });
        $queueComplete = "No Skills are currently training";
        if ($member->queue->isNotEmpty()) {
            $lastSkill = $member->queue->last();

            if (!is_null($lastSkill->pivot->finish_date)) {
                $queueComplete = Carbon::parse($lastSkill->pivot->finish_date)->toDateTimeString();
            }
        }
        return view('portal.queue', [
            'groupsTraining' => $groupsTraining,
            'spTraining' => $spTraining->sum(),
            'queueComplete' => $queueComplete
        ])->withMember($member);
    }

    public function switch ()
    {
        if (!Request::has('to') || !Request::get('return')) {
            Session::flash('alert', [
               "header" => "Unable to Swap to Character",
               'message' => "That character has not registered for this application. Please register that character with this application before attempting to swap to the character",
               'type' => 'danger',
               'close' => 1
            ]);
            return redirect(route('dashboard'));
        }
        $member = Auth::user()->alts->keyBy('id')->get(Request::get('to'));
        if (is_null($member)) {
            Session::flash('alert', [
               "header" => "Unknown Alt",
               'message' => "Alt with ID ". Request::get('to') . " is not known to this system. please try again",
               'type' => 'danger',
               'close' => 1
            ]);
            return redirect(Request::get('return'));
        }
        Auth::logout();
        Auth::login($member);
        Session::flash('alert', [
           "header" => "Switch Successful",
           'message' => "You are now logged in as ". Auth::user()->info->name,
           'type' => 'info',
           'close' => 1
        ]);
        return redirect(Request::get('return'));
    }

    public function welcome()
    {
        if (Request::isMethod('post')) {
            $validator = Validator::make(Request::all(), [
                'scopes' => "array|required|min:1"
            ]);
            if ($validator->failed()) {
                return redirect(route('welcome'))->withErrors($validator);
            }
            $selected = collect(Request::get('scopes'))->keys();
            $authorized = $selected->map(function($scope) {
                return collect(config('services.eve.scopes.core'))->recursive()->where('key', $scope)->first()->get('scope');
            });
            $authorized = $authorized->merge(collect(config('services.eve.scopes.core'))->where('required', true)->pluck('scope'))->sort()->values()->implode(' ');
            $hashedScopes = hash('sha1', $authorized);

            $state_hash = str_random(16);
            $state = collect([
                "redirectTo" => "welcome",
                "additionalData" => collect([
                    'authorizedScopesHash' => $hashedScopes
                ])
            ]);
            Session::put($state_hash, $state);
            $ssoUrl = config("services.eve.urls.sso")."/oauth/authorize?response_type=code&redirect_uri=" . route(config('services.eve.sso.callback')) . "&client_id=".config('services.eve.sso.id')."&state={$state_hash}&scope=".$authorized;
            return redirect($ssoUrl);
        }

        if (Request::has('state')) {
            if (!Session::has(Request::get('state'))) {
                Session::flash('alert', [
                    "header" => "Unable to Verify Response",
                    'message' => "Something went wrong parsing the response from the API",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect(route('welcome'));
            }
            $ssoResponse = Session::get(Request::get('state'));
            Session::forget(Request::get('state'));
            $hashedResponseScopes = hash('sha1', collect(explode(' ', $ssoResponse->get('Scopes')))->sort()->values()->implode(' '));
            if ($hashedResponseScopes !== $ssoResponse->get('authorizedScopesHash')) {
                Session::flash('alert', [
                    "header" => "Unable to Verify Requested Scopes",
                    'message' => "We are unable to verify that the scopes requested were the scopes that were authorized. Please use the link below to attempt the authentication again. If this error persists, contact IT.",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect(route('welcome'));
            }
            $getMemberData = $this->dataCont->getMemberData($ssoResponse->get('CharacterID'));
            $status = $getMemberData->get('status');
            $payload = $getMemberData->get('payload');
            if (!$status) {
                Session::flash('alert', [
                    "header" => "There was an issue authorizing the scopes you selected",
                    'message' => "We had an issue validating the scopes that you authenticated with. If the issue persists, please report the error via bitbucket using error id <strong>" . $payload->log_id . "</strong>",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect(route('welcome'));
            }

            $member = Member::firstOrNew(['id' => $payload->get('id')]);
            $member->fill([
                'main' => Auth::check() ? Auth::user()->id : $payload->get('id'),
                'scopes' => json_encode(explode(' ', $ssoResponse->get('Scopes'))),
                'access_token' => $ssoResponse->get('access_token'),
                'refresh_token' => $ssoResponse->get('refresh_token'),
                'raw_hash' => $ssoResponse->get('CharacterOwnerHash'),
                'hash' => hash('sha1', $ssoResponse->get('CharacterOwnerHash')),
                'disabled' => 0,
                'disabled_reason' => null,
                'disabled_timestamp' => null,
                'expires' => Carbon::now()->addSeconds($ssoResponse->get('expires_in'))->toDateTimeString()
            ]);
            $member->save();
            $dispatchedJobs = collect(); $now = now();
            if ($member->scopes->contains("esi-clones.read_clones.v1")) {
                $job = new \LevelV\Jobs\Member\GetMemberClones($member->id);
                $job->delay($now);
                $this->dispatch($job);
                $dispatchedJobs->push($job->getJobStatusId());
                $now = $now->addSeconds(1);
            }

            if ($member->scopes->contains("esi-clones.read_implants.v1")) {
                $job = new \LevelV\Jobs\Member\GetMemberImplants($member->id);
                $job->delay($now);
                $this->dispatch($job);
                $dispatchedJobs->push($job->getJobStatusId());
                $now = $now->addSeconds(1);
            }

            if ($member->scopes->contains("esi-skills.read_skills.v1")) {
                $job = new \LevelV\Jobs\Member\GetMemberAttributes($member->id);
                $job->delay($now);
                $this->dispatch($job);
                $dispatchedJobs->push($job->getJobStatusId());
                $now = $now->addSeconds(1);

                $job = new \LevelV\Jobs\Member\GetMemberSkillz($member->id);
                $job->delay($now);
                $this->dispatch($job);
                $dispatchedJobs->push($job->getJobStatusId());
                $now = $now->addSeconds(1);
            }
            if ($member->scopes->contains("esi-skills.read_skillqueue.v1")) {
                $job = new \LevelV\Jobs\Member\GetMemberSkillQueue($member->id);
                $job->delay($now);
                $this->dispatch($job);
                $dispatchedJobs->push($job->getJobStatusId());
                $now = $now->addSeconds(1);
            }
            $member->jobs()->attach($dispatchedJobs->toArray());
            if (Auth::user()->id == $payload->get('id')) {
                Session::flash('alert', [
                    "header" => "Welcome to " . config('app.name') ." ". Auth::user()->info->name,
                    'message' => "You account has been setup successfully. However, there is a lot of data we need to pull in from the API to properly display your profile to you, so bare with us while we talk with ESI to get that data for you. It shouldn't take long. You can use the Job Status module to the left to check on the status of these jobs. When you have zero (0) pending jobs, it is okay to load up your character, otherwise, one of pages you visit may crash because we don't have all the data yet.",
                    'type' => 'success',
                    'close' => 1
                ]);
            }

            if (Session::has('to')) {
                if (starts_with(Session::get('to'), url('/welcome'))) {
                    return redirect(route('dashboard'));
                }
                $to = Session::get('to');
                Session::forget(Session::get('to'));
                return redirect($to);
            }
            return redirect(route('dashboard'));
        }
        $requiredScopes = collect(config('services.eve.scopes.display.required'))->recursive();
        $optionalScopes = collect(config('services.eve.scopes.display.optional'))->recursive();
        return view('portal.welcome', [
            'required' => $requiredScopes,
            'optional' => $optionalScopes
        ]);
    }
}
