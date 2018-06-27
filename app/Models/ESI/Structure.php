<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use ESIK\Models\{MemberLocation, MemberWalletTransaction};
use ESIK\Models\ESI\Contract;

class Structure extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'structures';
    public $incrementing = false;
    protected static $unguarded = true;

    public function info ()
    {
        return $this->morphOne(MemberLocation::class, 'info');
    }

    public function clone ()
    {
        return $this->morphOne(Member::class, "clone");
    }

    public function jumpClones ()
    {
        return $this->morphOne(MemberJumpClones::class, "location");
    }

    public function bookmark_location()
    {
        return $this->morphOne(MemberBookmark::class, 'location');
    }

    public function wallet_transactions_location()
    {
        return $this->morphOne(MemberWalletTransaction::class, 'location');
    }

    public function start_location () {
        return $this->morphOne(Contract::class, 'start');
    }

    public function end_location () {
        return $this->morphOne(Contract::class, 'end');
    }
}
