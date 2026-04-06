<?php

namespace App\Jobs\Notifications;

use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFCMNotificationToTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 15;
    public string $queue = 'notifications';

    public function __construct(
        private array $tokens,
        private string $title,
        private string $body,
        private array $data = [],
        private array $options = [],
    ) {}

    public function handle(FCMService $fcmService): void
    {
        try {
            $result = $fcmService->sendToTokens(
                $this->tokens,
                $this->title,
                $this->body,
                $this->data,
                $this->options
            );

            Log::info('FCM Batch Notification Sent', [
                'title' => $this->title,
                'total_tokens' => count($this->tokens),
                'successful' => count($result['successful']),
                'failed' => count($result['failed']),
            ]);

            // Re-queue failed tokens for retry
            if (!empty($result['failed']) && $this->attempts() < $this->tries) {
                $failedTokens = array_column($result['failed'], 'token');
                SendFCMNotificationToTokens::dispatch(
                    $failedTokens,
                    $this->title,
                    $this->body,
                    $this->data,
                    $this->options
                )->delay(now()->addSeconds($this->backoff * $this->attempts()));
            }
        } catch (\Exception $e) {
            Log::error('FCM Batch Notification Job Failed', [
                'title' => $this->title,
                'token_count' => count($this->tokens),
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
        Log::error('FCM Batch Notification Job Failed After All Retries', [
            'title' => $this->title,
            'token_count' => count($this->tokens),
            'error' => $exception->getMessage(),
        ]);
    }
}
