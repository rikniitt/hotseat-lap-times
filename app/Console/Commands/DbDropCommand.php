<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbDropCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:drop {connection?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables in database';

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
        if (!$this->confirm('Drop all tables in database. Are you sure? ')) {
            $this->info('Canceled');
            return 0;
        }

        $connection = DB::connection($this->argument('connection'));

        $connection->transaction(function() use ($connection) {
            $pdo = $connection->getPdo();
            $tables = collect($connection->select('SHOW TABLES'))->map(function ($std) {
                return collect(array_values((array) $std))->first();
            })->toArray();

            foreach ($tables as $table) {
                $sql = 'DROP TABLE ' . $table;
                $this->comment('> ' . $sql);

                $pdo->query($sql);
            }
        });

        $this->info('Done');
    }
}
