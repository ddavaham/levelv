<?php

namespace LevelV\Traits;

use ESIK\Models\JobStatus;

trait Trackable
{
    /** @var int $statusId */
    protected $statusId;
    protected $progressNow = 0;
    protected $progressMax = 0;

    protected function setProgressMax($value)
    {
        $this->update(['progress_max' => $value]);
        $this->progressMax = $value;
    }

    protected function setProgressNow($value, $every = 1)
    {
        if ($value % $every == 0 || $value == $this->progressMax) {
            $this->update(['progress_now' => $value]);
        }
        $this->progressNow = $value;
    }

    protected function incrementProgress($offset = 1, $every = 1)
    {
        $value = $this->progressNow + $offset;
        $this->setProgressNow($value, $every);
    }

    protected function setInput(array $value)
    {
        $this->update(['input' => $value]);
    }

    protected function setOutput(array $value)
    {
        $this->update(['output' => $value]);
    }

    protected function update(array $data)
    {
        /** @var JobStatus $status */
        $status = JobStatus::find($this->statusId);

        if ($status != null) {
            return $status->update($data);
        }
        return null;
    }

    protected function prepareStatus(array $data = [])
    {
        $data = array_merge(["type" => static::class], $data);
        /** @var JobStatus $status */
        $status = JobStatus::create($data);

        $this->statusId = $status->id;
    }

    public function getJobStatusId()
    {
        if ($this->statusId == null) {
            throw new \Exception("Failed to get jobStatusId, have you called \$this->prepareStatus() in __construct() of Job?");
        }

        return $this->statusId;
    }
}
