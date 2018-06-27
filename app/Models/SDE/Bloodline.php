<?php

namespace LevelV\Models\SDE;

use Illuminate\Database\Eloquent\Model;

class Bloodline extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'bloodlines';
    public $incrementing = false;
    protected static $unguarded = true;
}
