<?php

namespace LevelV\Models\SDE;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'groups';
    public $incrementing = false;
    protected static $unguarded = true;
}
