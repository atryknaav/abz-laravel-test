<?php

namespace App\Console\Commands;

use App\Models\Token;
use Illuminate\Console\Command;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cleanup';
    protected $description = 'Delete expired tokens from the database';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Token::where('expires_at', '<', now())->delete();
        $this->info('Expired tokens cleaned up successfully.');
        
    }
}