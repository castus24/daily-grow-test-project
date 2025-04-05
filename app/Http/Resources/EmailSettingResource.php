<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $mail_driver
 * @property string $mail_host
 * @property int $mail_port
 * @property string $mail_username
 * @property string $mail_encryption
 * @property string $mail_from_address
 * @property string $mail_from_name
 * @property bool $is_active
 */
class EmailSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mail_driver' => $this->mail_driver,
            'mail_host' => $this->mail_host,
            'mail_port' => $this->mail_port,
            'mail_username' => $this->mail_username,
            'mail_encryption' => $this->mail_encryption,
            'mail_from_address' => $this->mail_from_address,
            'mail_from_name' => $this->mail_from_name,
            'is_active' => $this->is_active,
        ];
    }
}
