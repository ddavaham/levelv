<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use ESIK\Traits\HasCompositePrimaryKey;

class TypeDogmaEffect extends Model
{
    use HasCompositePrimaryKey;

    protected $primaryKey = ['type_id', 'effect_id'];
    protected $table = 'type_dogma_effects';
    public $incrementing = false;
    protected static $unguarded = true;
}
