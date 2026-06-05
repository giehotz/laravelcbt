<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Symfony\Component\Process\Process;

class ServeCommand extends BaseServeCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve the application on the PHP development server and start Vite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->components->info('Starting Vite development server...');

        // Start npm run dev in the background
        $viteProcess = Process::fromShellCommandline('npm run dev');
        $viteProcess->setTimeout(null);
        
        // Pass the output of Vite to the artisan console
        $viteProcess->start(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        // Ensure Vite process is stopped when artisan serve is stopped
        register_shutdown_function(function () use ($viteProcess) {
            if ($viteProcess->isRunning()) {
                $viteProcess->stop();
            }
        });

        // Call the parent handle to start the PHP server
        return parent::handle();
    }
}
