<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

use LevelV\Models\ESI\Character;

class SkillPlan extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'skillplans';
    public $incrementing = false;
    protected static $unguarded = true;

    protected $with = ['author'];

    public function getAttributesAttribute($attributes)
    {
        return collect(json_decode($attributes, true))->recursive();
    }

    public function getRemapsAttribute($remaps)
    {
        return collect(json_decode($remaps, true));
    }

    public function author()
    {
        return $this->hasOne(Character::class, 'id', 'author_id');
    }

    public function skillz()
    {
        return $this->hasMany(SkillPlanSkillz::class, 'plan_id')->with('info')->orderBy('skillplan_skillz.position', 'asc');
    }

    public function members()
    {
        return $this->hasMany(SkillPlanMembers::class, 'plan_id')->with('info');
    }

    public function isPrivate()
    {
        return !$this->is_public;
    }

    public function isPublic()
    {
        return $this->is_public;
    }
}
