<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-queue-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Jobs\TestJob::dispatch();
        $this->info('Job enviado para a fila!');
    }
}
