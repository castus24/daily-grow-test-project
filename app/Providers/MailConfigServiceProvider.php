<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use App\Models\EmailSetting;
use App\Services\EmailService;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            $emailSetting = EmailSetting::query()
                ->where('is_active', true)
                ->first();

            if ($emailSetting) {
                /** @var EmailSetting $emailSetting */
                EmailService::updateMailConfig($emailSetting);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load mail settings: ' . $e->getMessage());
        }
    }
}
