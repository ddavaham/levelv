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
        return $this->hasMany(SkillPlanSkillz::class, 'plan_id')->with('info')->orderBy('skillplan_skillz.position', 'asc');
    }

    public function getAttributesAttribute($attributes)
    {
        return collect(json_decode($attributes, true))->recursive();
    }

    public function getTotalSpAttribute($total_sp)
    {
        return number_format($total_sp, 0) . " SP";
    }

    public function getTrainingTimeAttribute($training_time)
    {
        $day = floor ($training_time / 1440);
        $hour = floor (($training_time - $day * 1440) / 60);
        $min = $training_time - ($day * 1440) - ($hour * 60);
        return number_format($day, 0). "d ".number_format($hour, 0)."h ".number_format($min, 0)."m";
    }
    public function getRemapsAttribute($remaps)
    {
        return collect(json_decode($remaps, true));
    public function members()
    {
        return $this->hasMany(SkillPlanMembers::class, 'plan_id')->with('info');
    }
    }
}
