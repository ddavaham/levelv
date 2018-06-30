<?php

namespace LevelV\Http\Controllers;

use Auth, Carbon, Request, Session, Validator;
use LevelV\Models\Member;

class PortalController extends Controller
{
    public function __construct()
    {
        $this->dataCont = new DataController;
    }

    public function dashboard ()
    {
        Auth::user()->load('jobs');
        return view('portal.dashboard');
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
                return collect(config('services.eve.scopes'))->recursive()->where('key', $scope)->first()->get('scope');
            });
            $authorized = $authorized->sort()->values()->implode(' ');
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
            // Session::forget(Request::get('state'));
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
            if ($ssoResponse->get('CharacterID') != Auth::user()->id) {
                Session::flash('alert', [
                    "header" => "Invalid Scope Authorization",
                    'message' => "The character that you logged in with is different then the character that you just authorized the scopes on. PLease try again and select the correct toon. In this case that is" . Auth::user()->info->name,
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect(route('welcome'));
            }
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
                'scopes' => json_encode(explode(' ', $ssoResponse->get('Scopes'))),
                'access_token' => $ssoResponse->get('access_token'),
                'refresh_token' => $ssoResponse->get('refresh_token'),
                'disabled' => 0,
                'disabled_reason' => null,
                'disabled_timestamp' => null,
                'expires' => Carbon::now()->addSeconds($ssoResponse->get('expires_in'))->toDateTimeString()
            ]);

            $member->save();
            $dispatchedJobs = collect(); $now = now();
            // if ($member->scopes->contains("esi-clones.read_clones.v1")) {
            //     $job = new \LevelV\Jobs\Member\GetMemberClones($member->id);
            //     $job->delay($now);
            //     $this->dispatch($job);
            //     $dispatchedJobs->push($job->getJobStatusId());
            //     $now = $now->addSeconds(1);
            // }
            //
            // if ($member->scopes->contains("esi-clones.read_implants.v1")) {
            //     $job = new \LevelV\Jobs\Member\GetMemberImplants($member->id);
            //     $job->delay($now);
            //     $this->dispatch($job);
            //     $dispatchedJobs->push($job->getJobStatusId());
            //     $now = $now->addSeconds(1);
            // }
            //
            // if ($member->scopes->contains("esi-skills.read_skills.v1")) {
            //     $job = new \LevelV\Jobs\Member\GetMemberAttributes($member->id);
            //     $job->delay($now);
            //     $this->dispatch($job);
            //     $dispatchedJobs->push($job->getJobStatusId());
            //     $now = $now->addSeconds(1);
            //
            //     $job = new \LevelV\Jobs\Member\GetMemberSkillz($member->id);
            //     $job->delay($now);
            //     $this->dispatch($job);
            //     $dispatchedJobs->push($job->getJobStatusId());
            //     $now = $now->addSeconds(1);
            // }
            if ($member->scopes->contains("esi-skills.read_skillqueue.v1")) {
                $job = new \LevelV\Jobs\Member\GetMemberSkillQueue($member->id);
                $job->delay($now);
                $this->dispatchNow($job);
                $dispatchedJobs->push($job->getJobStatusId());
                $now = $now->addSeconds(1);
            }
            dump("No redirect for you!!");
            abort(200);
            $member->jobs()->attach($dispatchedJobs->toArray());
            Session::flash('alert', [
                "header" => "Welcome to " . config('app.name') ." ". Auth::user()->info->name,
                'message' => "You account has been setup successfully. However, there is a lot of data we need to pull in from the API to properly display your profile to you, so bare with us while we talk with ESI to get that data for you. It shouldn't take long. You can use the Job Status module to the right to check on the status of these jobs. When you have zero (0) pending jobs, it is okay to load up your character, otherwise, one of pages you visit may crash because we don't have all the data yet.",
                'type' => 'success',
                'close' => 1
            ]);

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
        $scopes = collect(config('services.eve.scopes'))->recursive();
        return view('portal.welcome')->withScopes($scopes);
    }
}
