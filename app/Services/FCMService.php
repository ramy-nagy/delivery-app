<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Psr\Http\Client\ClientExceptionInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class FCMService
{
    private $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = storage_path('app/firebase-credentials.json');
            
            if (!file_exists($credentialsPath)) {
                throw new Exception('Firebase credentials file not found at: ' . $credentialsPath);
            }

            $factory = (new Factory())->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (Exception $e) {
            Log::error('FCM Service Initialization Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Send notification to a specific device token.
     *
     * @param string $token FCM device token
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param array $options Custom notification options (ttl, priority, etc.)
     * @return string Message ID on success
     * @throws Exception
     */
    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = [],
        array $options = []
    ): string {
        try {
            $message = $this->buildMessage($token, $title, $body, $data, $options, 'token');
            $result = $this->messaging->send($message);

            Log::info('FCM Message sent to token', [
                'token' => substr($token, 0, 20) . '...',
                'message_id' => $result,
                'title' => $title,
            ]);

            return $result;
        } catch (ClientExceptionInterface $e) {
            Log::error('FCM Send to Token Failed - Client Error', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('FCM Client Error: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('FCM Send to Token Failed - Server Error', [
                'token' => substr($token, 0, 20) . '...',
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('FCM Server Error: ' . $e->getMessage());
        }
    }

    /**
     * Send notification to multiple device tokens.
     *
     * @param array $tokens Array of FCM device tokens
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param array $options Custom notification options
     * @return array Result with successful and failed tokens
     */
    public function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        array $options = []
    ): array {
        $successful = [];
        $failed = [];

        foreach ($tokens as $token) {
            try {
                $messageId = $this->sendToToken($token, $title, $body, $data, $options);
                $successful[] = [
                    'token' => $token,
                    'message_id' => $messageId,
                ];
            } catch (Exception $e) {
                $failed[] = [
                    'token' => $token,
                    'error' => $e->getMessage(),
                ];
            }
        }

        Log::info('FCM Batch send completed', [
            'total' => count($tokens),
            'successful' => count($successful),
            'failed' => count($failed),
        ]);

        return [
            'successful' => $successful,
            'failed' => $failed,
            'total' => count($tokens),
        ];
    }

    /**
     * Send notification to a topic.
     *
     * @param string $topic Topic name
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data payload
     * @param array $options Custom notification options
     * @return string Message ID on success
     * @throws Exception
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = [],
        array $options = []
    ): string {
        try {
            $message = $this->buildMessage($topic, $title, $body, $data, $options, 'topic');
            $result = $this->messaging->send($message);

            Log::info('FCM Message sent to topic', [
                'topic' => $topic,
                'message_id' => $result,
                'title' => $title,
            ]);

            return $result;
        } catch (ClientExceptionInterface $e) {
            Log::error('FCM Send to Topic Failed - Client Error', [
                'topic' => $topic,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('FCM Client Error: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('FCM Send to Topic Failed - Server Error', [
                'topic' => $topic,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('FCM Server Error: ' . $e->getMessage());
        }
    }

    /**
     * Subscribe device token to a topic.
     *
     * @param string $topic Topic name
     * @param array $tokens Array of device tokens
     * @return array Result with subscribed and failed tokens
     * @throws Exception
     */
    public function subscribeToTopic(string $topic, array $tokens): array
    {
        try {
            // Firebase SDK handles subscriptions
            $this->messaging->subscribeToTopic($tokens, $topic);

            Log::info('FCM Tokens subscribed to topic', [
                'topic' => $topic,
                'token_count' => count($tokens),
            ]);

            return [
                'topic' => $topic,
                'tokens_subscribed' => count($tokens),
                'status' => 'success',
            ];
        } catch (Exception $e) {
            Log::error('FCM Topic Subscription Failed', [
                'topic' => $topic,
                'token_count' => count($tokens),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to subscribe to topic: ' . $e->getMessage());
        }
    }

    /**
     * Unsubscribe device token from a topic.
     *
     * @param string $topic Topic name
     * @param array $tokens Array of device tokens
     * @return array Result
     * @throws Exception
     */
    public function unsubscribeFromTopic(string $topic, array $tokens): array
    {
        try {
            $this->messaging->unsubscribeFromTopic($tokens, $topic);

            Log::info('FCM Tokens unsubscribed from topic', [
                'topic' => $topic,
                'token_count' => count($tokens),
            ]);

            return [
                'topic' => $topic,
                'tokens_unsubscribed' => count($tokens),
                'status' => 'success',
            ];
        } catch (Exception $e) {
            Log::error('FCM Topic Unsubscription Failed', [
                'topic' => $topic,
                'token_count' => count($tokens),
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Failed to unsubscribe from topic: ' . $e->getMessage());
        }
    }

    /**
     * Build a CloudMessage object.
     *
     * @param string $target Token or topic name
     * @param string $title Notification title
     * @param string $body Notification body
     * @param array $data Additional data
     * @param array $options Custom options
     * @param string $type 'token' or 'topic'
     * @return CloudMessage
     */
    private function buildMessage(
        string $target,
        string $title,
        string $body,
        array $data = [],
        array $options = [],
        string $type = 'token'
    ): CloudMessage {
        $notification = Notification::create($title, $body);

        // Create base message with notification and data
        $message = CloudMessage::new()
            ->withNotification($notification);

        // Set the target (token or topic)
        if ($type === 'token') {
            $message = $message->withToken($target);
        } elseif ($type === 'topic') {
            $message = $message->withTopic($target);
        }

        // Add data payload - ensure all keys and values are strings
        $stringData = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $stringData[(string)$key] = (string)$value;
            }
            $message = $message->withData($stringData);
        }

        // Set analytics label if provided
        if (!empty($options['analytics_label'])) {
            $message = $message->withFcmOptions([
                'analytics_label' => $options['analytics_label'],
            ]);
        }

        // Android-specific options
        if (isset($options['android']) || !isset($options['exclude_android'])) {
            $message = $message->withAndroidConfig([
                'priority' => $options['android_priority'] ?? 'high',
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => $options['click_action'] ?? '',
                    'tag' => $options['tag'] ?? 'default',
                ],
            ]);
        }

        // iOS-specific options
        if (isset($options['ios']) || !isset($options['exclude_ios'])) {
            $message = $message->withApnsConfig([
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'sound' => 'default',
                    ],
                ],
            ]);
        }

        // WebPush options - TTL in headers as integer
        if (isset($options['webpush']) || !isset($options['exclude_webpush'])) {
            $ttl = (int)($options['ttl'] ?? 86400); // Default 24 hours
            $message = $message->withWebpushConfig([
                'headers' => [
                    'TTL' => $ttl,
                    'Urgency' => $options['urgency'] ?? 'high',
                ],
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => $options['icon'] ?? '',
                ],
            ]);
        }

        return $message;
    }

    /**
     * Validate FCM token format.
     *
     * @param string $token FCM token to validate
     * @return bool
     */
    public static function isValidToken(string $token): bool
    {
        return !empty($token) && strlen($token) >= 100;
    }

    /**
     * Validate topic name.
     *
     * @param string $topic Topic name
     * @return bool
     */
    public static function isValidTopic(string $topic): bool
    {
        // Topic names must match: [a-zA-Z0-9-_.~%]
        return preg_match('/^[a-zA-Z0-9-_.~%]+$/', $topic) === 1;
    }
}
