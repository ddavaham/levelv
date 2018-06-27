<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use ESIK\Models\{MemberLocation, MemberWalletTransaction};
use ESIK\Models\ESI\Contract;

class Station extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'stations';
    public $incrementing = false;
    protected static $unguarded = true;

    public function location_info ()
    {
        return $this->morphOne(MemberLocation::class);
    }

    public function clone_info ()
    {
        return $this->morphOne(MemberLocation::class);
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

    public function nameDotlanFormat() {
        return implode('_', explode(' ', $this->name));
    }
}
