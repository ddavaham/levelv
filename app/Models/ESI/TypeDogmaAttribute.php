<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use LevelV\Traits\HasCompositePrimaryKey;

class TypeDogmaAttribute extends Model
{
    use HasCompositePrimaryKey;

    protected $primaryKey = ['type_id', 'attribute_id'];
    protected $table = 'type_dogma_attributes';
    public $incrementing = false;
    protected static $unguarded = true;
}
