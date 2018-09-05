<?php

namespace LevelV\Jobs\Member;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Log;
use LevelV\Models\Member;
use LevelV\Traits\Trackable;
use LevelV\Http\Controllers\DataController;

class GetMemberAttributes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public $id, $dataCont;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id)
    {
        $this->id = $id;
        $this->dataCont = new DataController;
        $this->prepareStatus();
        $this->setInput(['id' => $id]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $member = Member::findOrFail($this->id);
        $getMemberAttributes = $this->dataCont->getMemberAttributes($member);
        $status = $getMemberAttributes->get('status');
        $payload = $getMemberAttributes->get('payload');
        if (!$status) {
            if ($payload->get('code') >= 400) {
                Log::error($payload->get('message'));
            }
        }
        return $status;
    }
}
