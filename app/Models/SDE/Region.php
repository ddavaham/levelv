<?php

namespace LevelV\Models\SDE;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'regions';
    public $incrementing = false;
    protected static $unguarded = true;

    public function location()
    {
        return $this->morphOn(MemberBookmark::class, 'location');
    }

    public function nameDotlanFormat() {
        return implode('_', explode(' ', $this->name));
    }
}
