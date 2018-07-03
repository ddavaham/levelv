<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

use LevelV\Models\ESI\Type;

class SkillPlan extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'skillplans';
    public $incrementing = false;
    protected static $unguarded = true;

    public function skillz()
    {
        return $this->belongsToMany(Type::class, 'skillplan_skillz', 'plan_id', 'type_id')->withPivot('level', 'position')->orderBy('skillplan_skillz.position', 'asc');
    }
}
