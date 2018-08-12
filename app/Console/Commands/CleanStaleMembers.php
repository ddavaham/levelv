<?php

namespace LevelV\Console\Commands;

use Carbon;
use LevelV\Models\Member;
use Illuminate\Console\Command;

class CleanStaleMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean-stale-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Records that were created over but that are still null.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $deleteMembers = Member::where('created_at', '<', Carbon::now()->subHour())
        ->whereNull('main')->whereNull('total_sp')->whereNull('attributes')->whereNull('access_token')->whereNull('refresh_token')
        ->delete();

        $this->info($deleteMembers . " records removed");
        \Log::info($deleteMembers . " records removed");
    }
}
