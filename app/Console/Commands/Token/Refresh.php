<?php

namespace LevelV\Console\Commands\Token;

use Illuminate\Console\Command;

use LevelV\Models\Member;

class Refresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use current refresh tokens to get a new Access Token';

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
        Member::where('disabled', 0)->whereNotNull('refresh_token')->get()->chunk(50)->each(function ($chunk) {
            $chunk->each(function ($member) {
                $this->ssoCont->refresh($member);
            });
        });
    }
}
