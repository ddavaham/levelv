<?php

namespace LevelV\Models;

use Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use LevelV\Models\ESI\{Character, Type};

class Member extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'id';
    protected $table = 'members';
    public $incrementing = false;
    protected static $unguarded = true;

    protected $dates = [
        'expires'
    ];

    protected $with = ['info'];

    public function getScopesAttribute($scopes)
    {
        return collect(json_decode($scopes, true));
    }

    public function getAttributesAttribute($attributes)
    {
        $attributes = collect(json_decode($attributes, true));
        if ($attributes->has('last_remap_date')) {
            $attributes->put('last_remap_date', Carbon::parse($attributes->get('last_remap_date')));
        }
        if ($attributes->has('accrued_remap_cooldown_date')) {
            $attributes->put('accrued_remap_cooldown_date', Carbon::parse($attributes->get('accrued_remap_cooldown_date')));
        }
        return $attributes;
    }

    public function getImplantsAttribute($implants)
    {
        $implants = collect(json_decode($implants, true));
        return Type::whereIn('id', $implants->toArray())->with('implantAttributes')->get();
    }

    public function getRememberToken()
    {
        return null; // not supported
    }

    public function setRememberToken($value)
    {
        // not supported
    }

    public function getRememberTokenName()
    {
        return null; // not supported
    }

    /**
    * Overrides the method to ignore the remember token.
    */
    public function setAttribute($key, $value)
    {
        $isRememberTokenAttribute = $key == $this->getRememberTokenName();
        if (!$isRememberTokenAttribute)
        {
          parent::setAttribute($key, $value);
        }
    }

    // ****************************************************************************************************
    // *********************************** Member Data Relationships **************************************
    // ****************************************************************************************************
    public function info()
    {
        return $this->hasOne(Character::class, 'id', 'id');
    }

    public function clone()
    {
        return $this->morphTo('clone', 'clone_location_type', 'clone_location_id', 'id');
    }

    public function implants()
    {
        return $this->belongsToMany(Type::class, 'member_implants', 'member_id', 'type_id');
    }

    public function clones()
    {
        return $this->hasMany(MemberJumpClone::class, 'id', 'id');
    }

    public function jobs()
    {
        return $this->belongsToMany(JobStatus::class, 'member_jobs', 'member_id', 'job_id');
    }

    public function skillz()
    {
        return $this->belongsToMany(Type::class, 'member_skillz', 'id', 'skill_id')->withPivot('active_skill_level','trained_skill_level', 'skillpoints_in_skill');
    }
    public function queue()
    {
        return $this->belongsToMany(Type::class, 'member_skill_queue', 'id', 'skill_id')->using(MemberSkillQueue::class)->withPivot('queue_position', 'finished_level', 'level_start_sp', 'level_end_sp', 'training_start_sp', 'start_date', 'finish_date');
    }

}
