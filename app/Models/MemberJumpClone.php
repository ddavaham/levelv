<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

class MemberJumpClone extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'member_jump_clones';
    public $incrementing = false;
    protected static $unguarded = true;

    public function location ()
    {
        return $this->morphTo('clone_location', 'location_type', 'location_it', 'id');
    }
}
