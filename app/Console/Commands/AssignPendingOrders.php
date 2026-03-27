<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class AssignPendingOrders extends Command
{
    protected $signature = 'orders:assign-pending';
    protected $description = 'Assign drivers to pending orders';

    public function handle()
    {
        // Implement assignment logic
    }
}
