<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile';
    protected $description = 'Reconcile payments';

    public function handle()
    {
        // Implement reconciliation logic
    }
}
