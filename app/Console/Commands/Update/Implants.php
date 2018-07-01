<?php

namespace LevelV\Console\Commands\Update;

use Illuminate\Console\Command;

use Bus;
use LevelV\Models\Member;
use LevelV\Jobs\Member\GetMemberImplants;
use LevelV\Http\Controllers\DataController;

class Implants extends Command
{
    public $dataCont;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:implants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loops through enabled members and updates their skillqueue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->dataCont = new DataController;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = now(); $x=0;
        $members = Member::where('disabled', 0)->whereRaw("JSON_CONTAINS(scopes, '[\"esi-clones.read_implants.v1\"]')")->chunk(50, function ($chunk) use (&$now,&$x) {
            $chunk->each(function ($member) use (&$now,&$x) {
                $job = new GetMemberImplants($member->id);
                $job->delay($now);
                Bus::dispatch($job);
                $member->jobs()->attach($job->getJobStatusId());
                if ($x%10==0) {
                    $now->addSecond();
                }
                $x++;
            });
            sleep(3);
        });
    }
}
