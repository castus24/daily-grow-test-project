<?php

namespace App\Jobs;

use App\Enums\MailingRecipientStatusEnum;
use App\Enums\MailingStatusEnum;
use App\Models\Mailing;
use App\Mail\MailingEmail;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendMailingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Mailing $mailing;
    protected bool $hasErrors = false;

    /**
     * Create a new job instance.
     */
    public function __construct(Mailing $mailing)
    {
        $this->mailing = $mailing;
        $this->onQueue('mailings');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->mailing->status != MailingStatusEnum::SCHEDULED) {
            return;
        }

        foreach ($this->mailing->recipients as $recipient) {
            try {
                Mail::to($recipient->email)
                    ->send(new MailingEmail($this->mailing->content, $this->mailing->name));

                $this->mailing->recipients()->updateExistingPivot($recipient->id, [
                    'status' => MailingRecipientStatusEnum::SENT,
                    'sent_at' => now()
                ]);

                $this->mailing->increment('sent_count');
            } catch (Exception $e) {
                $this->hasErrors = true;

                Log::error('Failed to send mailing email: ' . $e->getMessage(), [
                    'mailing_id' => $this->mailing->id,
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $this->mailing->recipients()->updateExistingPivot($recipient->id, [
                    'status' => MailingRecipientStatusEnum::FAILED
                ]);
            }
        }

        $this->mailing->update([
            'status' => $this->hasErrors ? MailingStatusEnum::FAILED : MailingStatusEnum::SENT
        ]);
    }

    public function failed(Exception $exception): void
    {
        $this->mailing->update([
            'status' => MailingStatusEnum::FAILED
        ]);

        Log::critical('Mailing job failed completely: ' . $exception->getMessage(), [
            'mailing_id' => $this->mailing->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
