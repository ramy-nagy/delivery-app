<?php

namespace App\Jobs\Notifications;

use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFCMNotificationToTopic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;
    public string $queue = 'notifications';

    public function __construct(
        private string $topic,
        private string $title,
        private string $body,
        private array $data = [],
        private array $options = [],
    ) {}

    public function handle(FCMService $fcmService): void
    {
        try {
            $messageId = $fcmService->sendToTopic(
                $this->topic,
                $this->title,
                $this->body,
                $this->data,
                $this->options
            );

            Log::info('FCM Topic Notification Sent Successfully', [
                'topic' => $this->topic,
                'title' => $this->title,
                'message_id' => $messageId,
            ]);
        } catch (\Exception $e) {
            Log::error('FCM Topic Notification Job Failed', [
                'topic' => $this->topic,
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
        Log::error('FCM Topic Notification Job Failed After All Retries', [
            'topic' => $this->topic,
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);
    }
}
