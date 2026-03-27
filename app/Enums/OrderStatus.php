<?php
namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case PICKED_UP = 'picked_up';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
