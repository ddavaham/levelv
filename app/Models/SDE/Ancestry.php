<?php

namespace LevelV\Models\SDE;

use Illuminate\Database\Eloquent\Model;

class Ancestry extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'ancestries';
    public $incrementing = false;
    protected static $unguarded = true;
}
