<?php
namespace App\Broadcasting\Events;

class NotificationBroadcast
{
    public $userId;
    public $message;

    public function __construct($userId, $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }
}
