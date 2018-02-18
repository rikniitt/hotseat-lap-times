<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Exception\RuntimeException;

class DbConsoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:console {connection=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Open database console';

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
        $cmd = $this->getDbConsoleCmd();
        $process = new Process($cmd);

        $process->setTty(true)
                ->run();

        $this->info('Done');

        return $process->getExitCode();
    }

    private function getDbConsoleCmd()
    {
        $config = config('database');

        if ($this->argument('connection') === 'default') {
            $connection = $config['connections'][$config['default']];
        } else {
            $connection = $config['connections'][$this->argument('connection')];
        }

        $driver = $connection['driver'];

        switch ($driver) {
            case 'mysql':
                return sprintf(
                    "mysql -h'%s' -u'%s' -p'%s' %s",
                    $connection['host'],
                    $connection['username'],
                    $connection['password'],
                    $connection['database']
                );
                break;

            default:
                throw new RuntimeException('Console for driver ' . $driver . ' not implemented.');
                break;
        }
    }
}
