<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneExpiredTokens extends Command
{
    protected $signature = 'tokens:prune-expired';
    protected $description = 'Prune expired tokens';

    public function handle()
    {
        // Implement pruning logic
    }
}
