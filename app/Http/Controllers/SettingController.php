<?php

namespace LevelV\Http\Controllers;

use Auth, Carbon, Request, Session;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->ssoCont = new SSOController;
        $this->dataCont = new DataController;
    }

    public function index ()
    {
        return view('settings.index');
    }

    public function token ()
    {
        if (Request::isMethod('delete')) {
            Auth::user()->alts()->delete();
            Auth::user()->delete();
            Session::flash('alert', [
                'header' => "Token Deleted Successfully",
                'message' => "Your token has been successfully deleted from the system. Please login to register a new token and continue using the site",
                'type' => 'success',
                'close' => 1
            ]);
            return redirect(route('auth.login'));
        }
        return view('settings.token');
    }
}
