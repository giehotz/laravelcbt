<?php

namespace App\Console\Commands;

use App\Models\Cbt\Token;
use Illuminate\Console\Command;

class GenerateTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cbt:generate-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new CBT token automatically if auto is enabled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = Token::first();

        if ($token && $token->auto) {
            Token::generate();
            $this->info('New token generated successfully.');
        } else {
            $this->info('Auto generate is disabled or token not found. Skipping.');
        }
    }
}
