<?php

namespace LevelV\Http\Controllers;

use Auth, Carbon, Request, Session;
use LevelV\Models\Member;
use LevelV\Models\ESI\Character;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->dataCont = new DataController;
        $this->httpCont = new HttpController;
    }

    public function login()
    {
        if (Request::has('state') && Session::has(Request::get('state'))) {
            $ssoResponse = Session::get(Request::get('state'));
            Session::forget(Request::get('state'));
            $getMemberData = $this->dataCont->getMemberData($ssoResponse->get('CharacterID'), true);
            if (!$getMemberData->get('status')) {
                Session::flash('alert', [
                    "header" => "Unable to Retrieve Member Data",
                    'message' => "Unable to verify member data, please try again later.",
                    'type' => 'danger',
                    'close' => 1
                ]);
                return redirect(route('auth.login'));
            }
            $getMemberData = $getMemberData->get('payload');
            $member = Member::firstOrNew(['id' => $getMemberData->get('id')]);
            if ($member->exists) {
                Auth::login($member);
                if ($member->disabled) {
                    return redirect(route('welcome', ['reason' => "disabled"]));
                } else {
                    if (Session::has('to')) {
                        $to = Session::get('to');
                        Session::forget('to');
                        return redirect($to);
                    } else {
                        return redirect(route('dashboard'));
                    }
                }
            } else {
                $member->fill([
                    'raw_hash' => $ssoResponse->get('CharacterOwnerHash'),
                    'hash' => hash('sha1', $ssoResponse->get('CharacterOwnerHash')),
                ]);
                $member->save();
                Auth::login($member);
                return redirect(route('welcome'));
            }
        }
        $state_hash = str_random(16);
        $scopes = collect(config("services.eve.scopes"))->sort()->values()->implode(" ");
        $state = collect([
            "redirectTo" => "auth.login"
        ]);
        Session::put($state_hash, $state);
        $ssoUrl = config("services.eve.urls.sso")."/oauth/authorize?response_type=code&redirect_uri=" . route(config('services.eve.sso.callback')) . "&client_id=".config('services.eve.sso.id')."&state={$state_hash}";
        return view("auth.login", [
           'ssoUrl' => $ssoUrl
        ]);
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect(route('home'));
    }
}
