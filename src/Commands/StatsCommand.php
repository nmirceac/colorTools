<?php

namespace ColorTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use \App\ImageStore;

class StatsCommand extends Command
{
    use ConfirmableTrait;
    protected $signature = 'colortools:stats';
    protected $description = 'Fun stats';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $images = ImageStore::count();
        $this->info($images.' '.str_plural('image', $images).'');
        $this->info('All done!');
    }
}