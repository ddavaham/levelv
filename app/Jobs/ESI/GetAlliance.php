<?php

namespace LevelV\Jobs\ESI;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Log;
use LevelV\Traits\Trackable;
use LevelV\Http\Controllers\DataController;

class GetAlliance implements ShouldQueue
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
        $this->dataCont = new DataController();
        $this->prepareStatus();
        $this->setInput(['id' => $this->id]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $getAlliance = $this->dataCont->getAlliance($this->id);
        $status = $getAlliance->get('status');
        $payload = $getAlliance->get('payload');
        if (!$status) {
            if ($payload->get('code') >= 400) {
                Log::error($payload->get('message'));
            }
        }
        return $status;
    }
}
