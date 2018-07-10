<?php

namespace LevelV\Http\Controllers;

use Auth, Carbon, Request, Session, Validator;

class AltController extends Controller
{
    public function __construct()
    {
        $this->dataCont = new DataController;
    }


}
