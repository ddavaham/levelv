<?php

namespace LevelV\Console\Commands\Import;

use Illuminate\Console\Command;

use LevelV\Models\SDE\{Group};
use LevelV\Models\ESI\{Type};
use LevelV\Http\Controllers\DataController;

class Skillz extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:skillz';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all skillz.';

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
        $skillGroups = Group::where('category_id', 16)->where('published', 1)->get();
        if ($skillGroups->count() < 23) {
            dd("23 groups are suppose to be in the database to conduct this operation. Please run Import:SDE before runnig this command.");
        }
        $bar = $this->output->createProgressBar($skillGroups->count());
        $types = collect();
        $skillGroups->each(function ($group) use (&$types, &$bar) {
            $groupRequest = $this->dataCont->getGroup($group->id);
            $status = $groupRequest->get('status');
            $payload = $groupRequest->get('payload');
            if (!$status) {
                return true;
            }
            $bar->advance();
            $types = $types->merge(collect($payload->get('response')->types));
        });
        $count = $types->count();
        $now = now(); $x = 1;
        $bar = $this->output->createProgressBar($count);
        $types->each(function ($type) use ($count, &$now, &$x, $bar) {
            $getType = $this->dataCont->getType($type);
            $status = $getType->get('status');
            $payload = $getType->get('payload');
            if (!$status) {
                dump($payload->get('message'));
                return true;
            }
            $bar->advance();
            if ($x%20==0) {
                sleep(1);
            }
            $x++;
        });
    }
}
