<?php

namespace App\Jobs\Notifications;

use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFCMNotificationToToken implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        private string $token,
        private string $title,
        private string $body,
        private array $data = [],
        private array $options = [],
    ) {}

    public function handle(FCMService $fcmService): void
    {
        try {
            $fcmService->sendToToken($this->token, $this->title, $this->body, $this->data, $this->options);
        } catch (\Exception $e) {
            Log::error('FCM Token Notification Job Failed', [
                'token' => substr($this->token, 0, 20) . '...',
                'title' => $this->title,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                $this->release($this->backoff * $this->attempts());
            }
        }
    }

    public function failed(\Exception $exception): void
    {
        Log::error('FCM Token Notification Job Failed After All Retries', [
            'token' => substr($this->token, 0, 20) . '...',
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
