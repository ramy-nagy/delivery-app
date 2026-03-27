<?php
namespace App\Contracts\Services;

interface NotificationServiceInterface
{
    public function send($notification);
}
