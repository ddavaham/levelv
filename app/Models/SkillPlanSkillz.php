<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

use LevelV\Models\ESI\Type;

class SkillPlanSkillz extends Model
{
    use \LevelV\Traits\HasCompositePrimaryKey;

    protected $primaryKey = ['plan_id', 'position'];
    protected $table = 'skillplan_skillz';
    public $incrementing = false;
    protected static $unguarded = true;

    public function info()
    {
        return $this->hasOne(Type::class, 'id', 'type_id')->with('skillAttributes');
    }

    public function getRomAttribute()
    {
        return num2rom($this->level);
    }

}
