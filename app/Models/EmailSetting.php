<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $mail_driver
 * @property string $mail_host
 * @property int $mail_port
 * @property string $mail_username
 * @property string $mail_password
 * @property string $mail_encryption
 * @property string $mail_from_address
 * @property string $mail_from_name
 * @property bool $is_active
 */
class EmailSetting extends Model
{
    protected $table = 'email_settings';

    protected $fillable = [
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'mail_port' => 'integer'
    ];

    public static function getAllowedSorts(): array
    {
        return [
            'mail_from_address',
            'mail_username',
            'is_active'
        ];
    }

    public function getMailConfigAttribute(): array //todo check need
    {
        return [
            'driver' => $this->mail_driver,
            'host' => $this->mail_host,
            'port' => $this->mail_port,
            'username' => $this->mail_username,
            'password' => $this->mail_password,
            'encryption' => $this->mail_encryption,
            'from' => [
                'address' => $this->mail_from_address,
                'name' => $this->mail_from_name,
            ],
        ];
    }
}
