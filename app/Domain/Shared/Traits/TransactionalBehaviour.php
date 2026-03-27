<?php
namespace App\Domain\Shared\Traits;

use App\Domain\Shared\Services\TransactionCoordinator;
use Closure;

trait TransactionalBehaviour
{
    protected function inTransaction(Closure $callback): mixed
    {
        return app(TransactionCoordinator::class)->execute($callback);
    }

    protected function inSerializable(Closure $callback): mixed
    {
        return app(TransactionCoordinator::class)
            ->isolationLevel('SERIALIZABLE', $callback);
    }
}
