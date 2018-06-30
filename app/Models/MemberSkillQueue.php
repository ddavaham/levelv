<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

class MemberSkillQueue extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'member_skill_queue';
    public $incrementing = false;
    protected static $unguarded = true;
}
