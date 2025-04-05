<?php

namespace App\Http\Resources;

use App\Models\Mailing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $content
 * @property string $status
 * @property int $total_recipients
 * @property int $sent_count
 * @property string $scheduled_at
 * @property Mailing::recipients() $recipients
 * @property string $birth_date
 * @property string $created_at
 * @property string $updated_at
 */
class MailingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'status' => $this->status,
            'total_recipients' => $this->total_recipients,
            'sent_count' => $this->sent_count,
            'scheduled_at' => $this->scheduled_at ? Carbon::parse($this->scheduled_at)->format('Y-m-d H:i:s') : null,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($this->updated_at)->format('Y-m-d H:i:s'),
            'recipients' => $this->whenLoaded('recipients', function () {
                return $this->recipients->map(function ($recipient) {
                    return [
                        'id' => $recipient->id,
                        'name' => $recipient->name,
                        'email' => $recipient->email,
                        'phone' => $recipient->phone,
                        'birth_date' => $recipient->birth_date ? Carbon::parse($recipient->birth_date)->format('Y-m-d') : null,
                        'status' => $recipient->pivot->status,
                        'sent_at' => $recipient->pivot->sent_at ? Carbon::parse($recipient->pivot->sent_at)->format('Y-m-d H:i:s') : null,
                    ];
                });
            }),
        ];
    }
}
