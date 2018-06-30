<?php

namespace LevelV\Http\Controllers;

use LevelV\Models\Member;
use Carbon, Request, Session;
use LevelV\Http\Controllers\{DataController, HttpController};

class SSOController extends Controller
{
    public function __construct()
    {
        $this->dataCont = new DataController;
        $this->httpCont = new HttpController;
    }

    // Callback receive the redirect from CCP and processes the authorization code and retrieves an access token.
    public function callback() {
        if (!Request::has('code') || !Request::has('state') || !Session::has(Request::get('state'))) {
            Session::flash('alert', [
                "header" => "SSO Error",
                'message' => "Valid Authorization Parameters are missing or invalid. Please try again.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect(route('auth.login'));
        }
        $data = collect();
        $stateSession = Session::get(Request::get('state'));
        Session::forget(Request::get('state'));
        //CSRF Passed, lets verify the Authorization Code now

        $verifyAuthCode = $this->dataCont->verifyAuthCode(Request::get('code'));
        if (!$verifyAuthCode->get('status')) {
            Session::flash('alert', [
                "header" => "SSO Error",
                'message' => "Authorization with CCP SSO Failed. Please try again. If errors persists, contact David Davaham. These errors have been logged.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect(route($stateSession->get("redirectTo")));
        }
        $response = collect($verifyAuthCode->get('payload')->get('response'));


        //Authorization Code has been verified and we got back an Access Token and Refresh Token. Lets Verify those now and retrieve the some basic Character Data.
        $verifyAccessToken = $this->dataCont->verifyAccessToken($response->get('access_token'));
        if (!$verifyAccessToken->get('status')) {
            Session::flash('alert', [
                "header" => "SSO Error",
                'message' => "Access Token Verification with CCP SSO Failed. Please try again. If errors persists, contact David Davaham. These errors have been logged.",
                'type' => 'danger',
                'close' => 1
            ]);
            return redirect(route($stateSession->get("redirectTo")));
        }
        $response = $response->merge($verifyAccessToken->get('payload')->get('response'));
        $state = str_random(16);

        $response = $response->merge($stateSession->get('additionalData'));
        Session::put($state, $response);
        return redirect(route($stateSession->get("redirectTo"), ['state' => $state]));
    }

    public function refresh(Member $member)
    {
        $request = $this->httpCont->postRefreshToken($member->refresh_token);
        $status = $request->get('status');
        $payload = $request->get('payload');
        $response = collect($payload->get('response'));
        if (!$status) {
            $member->disabled = 1;
            $member->disabled_timestamp = Carbon::now();
            if ($response->get('error') && in_array($response->get('error'), ["invalid_grant", "invalid_token"]) ) {
                $member->disabled_reason = "Error: ". $response->get('error'). " || Error Description: ". $response->get('error_description');
            }

        } else {
            $member->access_token = $response->get('access_token');
            $member->expires = Carbon::now()->addSeconds($response->get('expires_in'));
        }
        $member->save();

        return $request;
    }

    public function revoke ($token, $type="refresh_token")
    {
        return $this->httpCont->postRevokeToken($token, $type);
    }
}
