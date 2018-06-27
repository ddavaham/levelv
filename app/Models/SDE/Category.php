<?php

namespace LevelV\Models\SDE;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'categories';
    public $incrementing = false;
    protected static $unguarded = true;
}
