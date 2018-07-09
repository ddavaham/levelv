<?php

namespace LevelV\Models\Pivot;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MemberSkillQueue extends Pivot
{
    protected $dates = [
        'start_date', 'finish_date'
    ];
}
