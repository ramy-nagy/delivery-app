<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class CancelStaleOrders extends Command
{
    protected $signature = 'orders:cancel-stale';
    protected $description = 'Cancel stale orders';

    public function handle()
    {
        // Implement cancellation logic
    }
}
