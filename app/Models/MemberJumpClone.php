<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

class MemberClone extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'member_jump_clones';
    public $incrementing = false;
    protected static $unguarded = true;
}
