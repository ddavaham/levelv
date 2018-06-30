<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'requests';
    public $incrementing = false;
    protected static $unguarded = true;
}
