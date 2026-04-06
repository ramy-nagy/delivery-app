<?php

namespace App\Console\Commands;

use App\Infrastructure\Repositories\FCMRepository;
use Illuminate\Console\Command;

class CleanupStaleDeviceTokens extends Command
{
    protected $signature = 'fcm:cleanup-stale-tokens';

    protected $description = 'Remove stale FCM device tokens (not used for 30+ days)';

    public function handle(FCMRepository $fcmRepository): int
    {
        $this->info('Cleaning up stale FCM device tokens...');

        $disabled = $fcmRepository->cleanupStaleTokens();

        $this->info("✓ Disabled {$disabled} stale device tokens");

        return self::SUCCESS;
    }
}
