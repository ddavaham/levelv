<?php

namespace LevelV\Console\Commands\Import;

use Illuminate\Console\Command;
use LevelV\Models\SDE\{Ancestry, Bloodline, Category, Constellation, Faction, Group, Race, Region};

class Reset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the SDE Tables in the database.';

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
        collect([Ancestry::class, Bloodline::class, Category::class, Constellation::class, Group::class, Race::class, Region::class])->each(function ($class) {
            $class::whereNotNull('id')->delete();
        });
    }
}
