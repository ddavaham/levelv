<?php

namespace LevelV\Models\ESI;

use Illuminate\Database\Eloquent\Model;

use LevelV\Models\SDE\Group;

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
        return $this->hasMany(TypeDogmaAttribute::class, 'type_id')->whereIn('attribute_id', config('services.eve.dogma.attributes.skillz.all'));
    }

    public function implantAttributes()
    {
        return $this->hasMany(TypeDogmaAttribute::class, 'type_id')->whereIn('attribute_id', config('services.eve.dogma.attributes.implants.all'));
    }

    public function group()
    {
        return $this->belongsTo(Group::class,  'group_id');
    }
}
