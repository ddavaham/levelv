<?php

namespace LevelV\Jobs\Member;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use LevelV\Models\Member;
use LevelV\Traits\Trackable;
use LevelV\Http\Controllers\DataController;

class GetMemberSkillQueue implements ShouldQueue
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
        $getMemberSkillQueue = $this->dataCont->getMemberSkillQueue($member);
        $status = $getMemberSkillQueue->get('status');
        $payload = $getMemberSkillQueue->get('payload');
        if (!$status) {
            throw new \Exception($payload->get('message'), 1);
        }
    }
}
