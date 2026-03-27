<?php
namespace App\Application\Orders\Actions;

use App\Application\Orders\Dto\AssignDriverDto;
use App\Domain\Delivery\Exceptions\DriverUnavailableException;
use App\Domain\Delivery\Services\DriverMatchingService;
use App\Domain\Orders\Exceptions\OrderNotFoundException;
use App\Domain\Orders\Services\OrderService;
use Illuminate\Support\Facades\DB;

class AssignDriverAction
{
    public function __construct(
        private OrderService $orderService,
        private DriverMatchingService $driverMatchingService
    ) {}

    /**
     * Assign a driver to an order.
     *
     * @param AssignDriverDto $dto
     * @return Order
     */
    public function handle(AssignDriverDto $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            $order = $this->orderService->findById($dto->orderId);
            if ($order === null) {
                throw new OrderNotFoundException;
            }
            $driver = $this->driverMatchingService->findAvailableDriver($dto->orderId);
            if ($driver === null) {
                throw new DriverUnavailableException;
            }

            return $this->orderService->assignDriver($order, $driver);
        });
    }
}
