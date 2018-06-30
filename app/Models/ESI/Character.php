<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use LevelV\Models\MemberWalletTransaction;
use LevelV\Models\SDE\{Ancestry, Bloodline, Race};

class Character extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'characters';
    public $incrementing = false;
    protected static $unguarded = true;

    protected $dates = [
        'birthday', 'cached_until'
    ];

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'id');
    }

    public function corporation ()
    {
        return $this->hasOne(Corporation::class, 'id', 'corporation_id');
    }

    public function alliance ()
    {
        return $this->hasOne(Alliance::class, 'id', 'alliance_id');
    }

    public function ancestry()
    {
        return $this->hasOne(Ancestry::class, 'id', 'ancestry_id');
    }
    public function bloodline()
    {
        return $this->hasOne(Bloodline::class, 'id', 'bloodline_id');
    }
    public function race ()
    {
        return $this->hasOne(Race::class, 'id', "race_id");
    }
}
