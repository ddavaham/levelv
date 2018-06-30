<?php

namespace LevelV\Providers;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

use Carbon;
use LevelV\Models\JobStatus;

class JobStatusProvider extends ServiceProvider
{
    public function boot()
    {
	    /** @var JobStatus $entityClass */
	    $entityClass = app()->getAlias(JobStatus::class);

        // Add Event listeners
        app(QueueManager::class)->before(function (JobProcessing $event) use ($entityClass){
            $this->updateJobStatus($event->job, [
                'status' => $entityClass::STATUS_EXECUTING,
                'job_id' => $event->job->getJobId(),
                'attempts' => $event->job->attempts(),
                'queue' => $event->job->getQueue(),
                'started_at' => Carbon::now()
            ]);
        });
        app(QueueManager::class)->after(function (JobProcessed $event) use($entityClass){
            $this->updateJobStatus($event->job, [
                'status' => $entityClass::STATUS_FINISHED,
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now()
            ]);
        });
        app(QueueManager::class)->failing(function (JobFailed $event) use ($entityClass){
            $this->updateJobStatus($event->job, [
                'status' => $entityClass::STATUS_FAILED,
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now()
            ]);
        });
        app(QueueManager::class)->exceptionOccurred(function (JobExceptionOccurred $event) use($entityClass) {
            $this->updateJobStatus($event->job, [
                'status' => $entityClass::STATUS_FAILED,
                'attempts' => $event->job->attempts(),
                'finished_at' => Carbon::now(),
                'output' => json_encode(['message' => $event->exception->getMessage()])
            ]);
        });
    }

    private function updateJobStatus(Job $job, array $data)
    {
        try {
            $payload = $job->payload();
            $jobStatus = unserialize($payload['data']['command']);

            if (!is_callable([$jobStatus, 'getJobStatusId'])) {
                return null;
            }

            $jobStatusId = $jobStatus->getJobStatusId();

	        /** @var JobStatus $entityClass */
	        $entityClass = app()->getAlias(JobStatus::class);

	        $jobStatus = $entityClass::where('id', '=', $jobStatusId);

            return $jobStatus->update($data);
        } catch (\Exception $e) {
            activity(__METHOD__)->log($e->getMessage());
            return null;
        }
    }
}
