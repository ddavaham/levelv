<?php

namespace LevelV\Models\SDE;

use Illuminate\Database\Eloquent\Model;

class Constellation extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'constellations';
    public $incrementing = false;
    protected static $unguarded = true;

    public function location()
    {
        return $this->morphOn(MemberBookmark::class, 'location');
    }

    public function region()
    {
        return $this->hasOne(Region::class, 'id', 'region_id');
    }
}
