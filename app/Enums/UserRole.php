<?php
namespace App\Enums;

enum UserRole: string
{
    case CUSTOMER = 'customer';
    case DRIVER = 'driver';
    case RESTAURANT = 'restaurant';
    case ADMIN = 'admin';
}
