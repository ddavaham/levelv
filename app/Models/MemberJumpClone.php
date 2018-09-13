<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

use LevelV\Models\ESI\Type;

class MemberJumpClone extends Model
{
    protected $primaryKey = 'clone_id';
    protected $table = 'member_jump_clones';
    public $incrementing = false;
    protected static $unguarded = true;

    protected $with = [
        'location'
    ];

    public function location ()
    {
        return $this->morphTo('location', 'location_type', 'location_id', 'id');
    }

    public function implants ()
    {
        return $this->belongsToMany(Type::class, 'member_jump_clone_implants', 'clone_id', 'implant_id');
    }

}
