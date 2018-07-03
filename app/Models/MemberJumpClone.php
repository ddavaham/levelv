<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

use LevelV\Models\ESI\Type;

class MemberJumpClone extends Model
{
    protected $primaryKey = 'id';
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

    public function getImplantsAttribute($implants)
    {
        $implants = collect(json_decode($implants, true));
        return Type::whereIn('id', $implants->toArray())->with('implantAttributes')->get();
    }
}
