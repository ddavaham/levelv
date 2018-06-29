<?php

namespace LevelV\Jobs\ESI;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use LevelV\Traits\Trackable;
use LevelV\Http\Controllers\DataController;

class GetStructure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public $memberId, $id, $dataCont;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $memberId, int $id)
    {
        $this->memberId = $memberId;
        $this->id = $id;
        $this->dataCont = new DataController();
        $this->prepareStatus();
        $this->setInput(['memberId' => $memberId, 'id' => $id]);
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $member = Member::findOrFail($this->memberId);
        $getSystem = $this->dataCont->getStructure($member, $this->id);
        $status = $getSystem->get('status');
        $payload = $getSystem->get('payload');
        if (!$status) {
            throw new \Exception($payload->get('message'), 1);
        }
    }
}
