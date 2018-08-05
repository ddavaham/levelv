<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

class SkillPlanMembers extends Model
{
    use \LevelV\Traits\HasCompositePrimaryKey;

    protected $primaryKey = ['plan_id', 'position'];
    protected $table = 'skillplan_members';
    public $incrementing = false;
    protected static $unguarded = true;

    public function info()
    {
        return $this->morphTo('info', 'member_type', 'member_id', 'id');
    }
}
