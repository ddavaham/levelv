<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

class MemberFittings extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'member_fittings';
    public $incrementing = false;
    protected static $unguarded = true;
}
