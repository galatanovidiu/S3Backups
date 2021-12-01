<?php

namespace Galatanovidiu\S3Backups\Commands;

use Carbon\Carbon;
use Galatanovidiu\S3Backups\S3Backups;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's3Backup:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backing up selected directories on Amazon S3';

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
     * @return int
     */
    public function handle()
    {

        (new S3Backups())->doBackup();

        echo "Backup completed at ". Carbon::now()->toDateTimeString()."\n";
        return '';
    }
}
