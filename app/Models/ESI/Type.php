<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use ESIK\Models\SDE\Group;

class Type extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'types';
    public $incrementing = false;
    protected static $unguarded = true;

    public function skillz()
    {
        return $this->belongsToMany(Type::class, 'type_skillz', 'type_id', 'id')->withPivot('value');
    }

    public function attributes()
    {
        return $this->hasMany(TypeDogmaAttribute::class, 'type_id');
    }

    public function skillAttributes()
    {
        return $this->hasMany(TypeDogmaAttribute::class, 'type_id')->whereIn('attribute_id', [182,183,184,1285,1289,1290,277,278,279,1286,1287,1288]);
    }

    public function effects()
    {
        return $this->hasMany(TypeDogmaEffect::class, 'type_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class,  'group_id');
    }
}
