<?php

namespace LevelV\Models\SDE;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'races';
    public $incrementing = false;
    protected static $unguarded = true;
}
