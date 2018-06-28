<?php

namespace LevelV\Http\Controllers;

use Auth, Carbon, Request, Session, Validator;
use ESIK\Models\Member;
use ESIK\Models\SDE\Group;
use ESIK\Models\ESI\{Station, System,Type};
use ESIK\Jobs\Members\GetMemberAssets;
use ESIK\Http\Controllers\DataController;

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
}
