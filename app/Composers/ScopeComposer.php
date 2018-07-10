<?php

namespace LevelV\Composers;

use Auth, Route;
use LevelV\Models\Member;

class ScopeComposer
{
    public function compose ($view) {
        if (Auth::check()) {
            if (Route::getFacadeRoot()->current()->hasParameter('member')) {
                $memberId = Route::getFacadeRoot()->current()->parameter('member');
                $member = Member::findOrFail($memberId);
                if (Auth::user()->id == $memberId) {
                    $view->with('scopes', Auth::user()->scopes);
                } else {
                    // $accessee = Auth::user()->accessee->keyBy('id')->get($memberId);
                    // $accessableScopes = collect(json_decode($accessee->pivot->access, true));
                    // $view->with('scopes', $accessableScopes);
                    $alt = Auth::user()->alts->keyBy('id')->get($memberId);
                    $view->with('scopes', $alt->scopes);
                }
            }
        }
        $view->with('currentRouteName', Route::currentRouteName());
    }
}
