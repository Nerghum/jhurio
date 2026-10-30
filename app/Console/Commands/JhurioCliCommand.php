<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class JhurioCliCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jhurio:cli {cli_args?* : Arguments and subcommands to pass to jhurio-cli}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Jhurio Store API CLI tool';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $args = $this->argument('cli_args') ?? [];
        $scriptPath = base_path('scripts/jhurio_cli.py');

        $process = new Process(array_merge(['python3', $scriptPath], $args));
        $process->setTimeout(60);

        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode() ?? 0;
    }
}
