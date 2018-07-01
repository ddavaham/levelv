<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use LevelV\Models\{MemberLocation, MemberWalletTransaction};
use LevelV\Models\ESI\Contract;

class Station extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'stations';
    public $incrementing = false;
    protected static $unguarded = true;

    public function info ()
    {
        return $this->morphOne(MemberLocation::class, 'info');
    }

    public function death_clone ()
    {
        return $this->morphOne(Member::class, "death_clone");
    }

    public function clone_location ()
    {
        return $this->morphOne(MemberJumpClones::class, "clone_location");
    }
}
