<?php

namespace App\Providers;

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
            // Загружаем активные настройки почты из базы данных
            $emailSetting = EmailSetting::where('is_active', true)->first();
            
            if ($emailSetting) {
                // Обновляем конфигурацию почты
                EmailService::updateMailConfig($emailSetting);
            }
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            \Log::error('Failed to load mail settings: ' . $e->getMessage());
        }
    }
} 