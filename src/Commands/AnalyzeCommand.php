<?php

namespace ColorTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use \App\ImageStore;

class AnalyzeCommand extends Command
{
    use ConfirmableTrait;
    protected $signature = 'colortools:analyze {--redo}';
    protected $description = 'Analyze stored images';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $images = ImageStore::count();

        $count = 0;

        foreach(ImageStore::all() as $image) {
            if($image->analyzed) {
                if($this->option('redo', false)) {
                    $this->info('Image # '.$image->id.' - "'.$image->name.'" already analyzed - reanalyzing');
                    $image->analyze(true);
                    $count++;
                } else {
                    $this->comment('Image # '.$image->id.' - "'.$image->name.'" already analyzed');
                    continue;
                }
            } else {
                $this->info('Image # '.$image->id.' - "'.$image->name.'" - analyzing');
                $image->analyze();
                $count++;
            }
        }


        $this->info($count.' '.str_plural('image', $count).' analyzed');
        $this->info('All done!');
    }
}