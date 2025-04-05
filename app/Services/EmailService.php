<?php

namespace App\Services;

use App\Models\EmailSetting;
use Illuminate\Support\Facades\Config;

class EmailService
{
    public static function getMailConfig(EmailSetting $setting): array
    {
        return [
            'driver' => $setting->mail_driver,
            'host' => $setting->mail_host,
            'port' => $setting->mail_port,
            'username' => $setting->mail_username,
            'password' => $setting->mail_password,
            'encryption' => $setting->mail_encryption,
            'from' => [
                'address' => $setting->mail_from_address,
                'name' => $setting->mail_from_name,
            ],
        ];
    }

    public static function getDefaultConfigs(): mixed
    {
        return config('mail_servers');
    }

    public static function updateMailConfig(EmailSetting $setting): void
    {
        $config = self::getMailConfig($setting);

        foreach ($config as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    Config::set("mail.{$key}.{$subKey}", $subValue);
                }
            } else {
                Config::set("mail.{$key}", $value);
            }
        }
    }
}
