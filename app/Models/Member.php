<?php

namespace LevelV\Models;

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

    public function jumpClones()
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
    public function skillQueue()
    {
        return $this->belongsToMany(Type::class, 'member_skill_queue', 'id', 'skill_id')->withPivot('queue_position', 'finished_level', 'level_start_sp', 'level_end_sp', 'training_start_sp', 'start_date', 'finish_date');
    }

}
