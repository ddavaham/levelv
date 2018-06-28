<?php

namespace LevelV\Models;

use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    const STATUS_QUEUED = 'queued';
    const STATUS_EXECUTING = 'executing';
    const STATUS_FINISHED = 'finished';
    const STATUS_FAILED = 'failed';

    protected $primaryKey = 'id';
    protected $table = 'job_statuses';
    protected static $unguarded = true;

    public $dates = ['started_at', 'finished_at'];

    /* Accessor */
    public function getInputAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getOutputAttribute($value)
    {
        return json_decode($value, true);
    }

    public function getProgressPercentageAttribute()
    {
        return $this->progress_max != 0 ? round(100 * $this->progress_now / $this->progress_max) : 0;
    }

    public function getIsEndedAttribute()
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_FINISHED]);
    }

    public function getIsFinishedAttribute()
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function getIsFailedAttribute()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getIsExecutingAttribute()
    {
        return $this->status === self::STATUS_EXECUTING;
    }

    public function getIsQueuedAttribute()
    {
        return $this->status === self::STATUS_QUEUED;
    }

    /* Mutator */
    public function setInputAttribute($value)
    {
        $this->attributes['input'] = json_encode($value);
    }

    public function setOutputAttribute($value)
    {
        $this->attributes['output'] = json_encode($value);
    }

    public static function getAllowedStatuses()
    {
        return [
            self::STATUS_QUEUED,
            self::STATUS_EXECUTING,
            self::STATUS_FINISHED,
            self::STATUS_FAILED
        ];
    }
}
