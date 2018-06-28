<?php

namespace LevelV\Http\Controllers;

use Carbon, Request, Session;

class PublicController extends Controller
{
    public function __construct()
    {
        $this->dataCont = new DataController;
    }

    public function home()
    {
        return redirect(route('auth.login'));
    }
}
