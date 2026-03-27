<?php
namespace App\Domain\Shared\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class TransactionCoordinator
{
    private int $level = 0;

    public function execute(\Closure $callback): mixed
    {
        if ($this->level === 0) {
            return DB::transaction($callback);
        }

        // Nested transaction
        $savepoint = "sp_{$this->level}";
        DB::statement("SAVEPOINT {$savepoint}");
        $this->level++;

        try {
            $result = $callback();
            $this->level--;
            return $result;
        } catch (Throwable $e) {
            $this->level--;
            DB::statement("ROLLBACK TO SAVEPOINT {$savepoint}");
            throw $e;
        }
    }

    public function isolationLevel(string $level, \Closure $callback): mixed
    {
        DB::statement("SET TRANSACTION ISOLATION LEVEL {$level}");

        try {
            return DB::transaction($callback);
        } finally {
            DB::statement("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");
        }
    }
}
