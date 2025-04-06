<?php

namespace App\Http\Controllers\Api\Mailing;

use App\Enums\MailingRecipientStatusEnum;
use App\Enums\MailingSendTypeEnum;
use App\Enums\MailingStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\MailingRequest;
use App\Http\Resources\MailingResource;
use App\Jobs\SendMailingJob;
use App\Mail\MailingEmail;
use App\Models\Client;
use App\Models\Mailing;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MailingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 10);

        $query = QueryBuilder::for(Mailing::class)
            ->allowedFilters(Mailing::getAllowedFilters())
            ->allowedSorts(Mailing::getAllowedSorts())
            ->with('recipients')
            ->paginate($request->input('itemsPerPage', $perPage));

        return MailingResource::collection($query);
    }

    public function store(MailingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Log::info('Creating mailing', [
            'send_type' => $validated['send_type'],
            'recipients_count' => $validated['send_to_all'] ? 'all' : count($validated['client_ids'])
        ]);

        $recipients = $validated['send_to_all']
            ? Client::all()
            : Client::query()->whereIn('id', $validated['client_ids'])->get();

        $status = $this->determineMailingStatus($validated);

        Log::info('Determined mailing status', [
            'status' => $status,
            'send_type' => $validated['send_type'],
            'has_scheduled_at' => !empty($validated['send_type'])
        ]);

        if ($validated['send_type'] === MailingSendTypeEnum::REGULAR) {
            $this->scheduleRegularMailing($recipients, $validated['name'], $validated['content']);

            return response()->json(['message' => 'Регулярная рассылка запланирована']);
        }

        $mailing = Mailing::query()->create([
            'name' => $validated['name'],
            'content' => $validated['content'],
            'status' => MailingStatusEnum::SCHEDULED,
            'total_recipients' => $recipients->count(),
            'scheduled_at' => $validated['scheduled_at']
        ]);

        $mailing->recipients()->attach(
            $recipients->pluck('id')->mapWithKeys(function ($id) {
                return [$id => ['status' => MailingRecipientStatusEnum::PENDING]];
            })->toArray()
        );

        if ($validated['send_type'] === MailingSendTypeEnum::NOW) {
            SendMailingJob::dispatch($mailing)->onQueue('mailings');
        } elseif ($validated['send_type'] === MailingSendTypeEnum::AUTO && $validated['scheduled_at']) {
            SendMailingJob::dispatch($mailing)
                ->delay(Carbon::parse($validated['scheduled_at']))
                ->onQueue('mailings');
        }

        return response()->json([
            'message' => 'Рассылка создана',
            'data' => new MailingResource($mailing)
        ], ResponseAlias::HTTP_CREATED);
    }

    public function show(Mailing $mailing): JsonResponse
    {
        return response()->json($mailing->load('recipients'));
    }

    public function update(MailingRequest $request, Mailing $mailing): JsonResponse
    {
        if ($mailing->status == MailingStatusEnum::SENT) {
            return response()->json([
                'error' => 'Нельзя редактировать отправленную рассылку'
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $status = $validated['status'];
            if ($validated['send_type'] === MailingSendTypeEnum::NOW) {
                $status = MailingStatusEnum::SCHEDULED;
            } elseif ($validated['send_type'] === MailingSendTypeEnum::AUTO && !empty($validated['scheduled_at'])) {
                $status = MailingStatusEnum::SCHEDULED;
            }

            $mailing->update([
                'name' => $validated['name'],
                'content' => $validated['content'],
                'status' => $status,
                'scheduled_at' => $validated['scheduled_at'] ?? null
            ]);

            $recipients = $this->getRecipients($validated);
            $mailing->recipients()->sync(
                $recipients->map(function ($client) {
                    return [
                        'client_id' => $client->id,
                        'status' => MailingRecipientStatusEnum::PENDING
                    ];
                })->toArray()
            );

            $mailing->update(['total_recipients' => $recipients->count()]);

            if ($status === MailingStatusEnum::SCHEDULED) {
                $this->scheduleMailing($mailing);
            }

            DB::commit();
            return response()->json($mailing->load('recipients'));
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Не удалось обновить рассылку: ' . $e->getMessage()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Mailing $mailing): JsonResponse
    {
        if ($mailing->status == 'sent') {
            return response()->json([
                'error' => 'Нельзя удалить отправленную рассылку'
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $mailing->delete();
            return response()->json(null, ResponseAlias::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error('Failed to delete mailing: ' . $e->getMessage(), [
                'mailing_id' => $mailing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Не удалось удалить рассылку: ' . $e->getMessage()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function send(Mailing $mailing): JsonResponse
    {
        if ($mailing->status != 'draft') {
            return response()->json([
                'error' => 'Можно отправлять только черновики'
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->sendMailing($mailing);
    }

    private function sendMailing(Mailing $mailing): JsonResponse
    {
        $mailing->update(['status' => MailingStatusEnum::SCHEDULED]);

        foreach ($mailing->recipients as $recipient) {
            try {
                Mail::to($recipient->email)
                    ->send(new MailingEmail($mailing->content, $mailing->name));

                $mailing->recipients()->updateExistingPivot($recipient->id, [
                    'status' => MailingRecipientStatusEnum::SENT,
                    'sent_at' => now()
                ]);

                $mailing->increment('sent_count');
            } catch (Exception $e) {
                Log::error('Failed to send mailing email: ' . $e->getMessage(), [
                    'mailing_id' => $mailing->id,
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $mailing->recipients()->updateExistingPivot($recipient->id, [
                    'status' => MailingRecipientStatusEnum::FAILED
                ]);
            }
        }

        $mailing->update(['status' => MailingStatusEnum::SENT]);
        return response()->json($mailing->load('recipients'));
    }

    private function scheduleMailing(Mailing $mailing): void
    {
        Log::info('Starting scheduleMailing', [
            'mailing_id' => $mailing->id,
            'scheduled_at' => $mailing->scheduled_at
        ]);

        if (!$mailing->scheduled_at) {
            Log::warning('No scheduled_at date provided', [
                'mailing_id' => $mailing->id
            ]);

            return;
        }

        try {
            $scheduledAt = Carbon::parse($mailing->scheduled_at)->setTimezone(config('app.timezone'));

            Log::info('Parsed scheduled time', [
                'mailing_id' => $mailing->id,
                'original_time' => $mailing->scheduled_at,
                'parsed_time' => $scheduledAt->toDateTimeString(),
                'timezone' => config('app.timezone')
            ]);

            if ($scheduledAt->isPast()) {
                Log::warning('Scheduled time is in the past', [
                    'mailing_id' => $mailing->id,
                    'scheduled_at' => $scheduledAt->toDateTimeString()
                ]);
                return;
            }

            $job = SendMailingJob::dispatch($mailing)
                ->delay($scheduledAt)
                ->onQueue('mailings');

            Log::info('Mailing job dispatched', [
                'mailing_id' => $mailing->id,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'job_id' => $job->id ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to schedule mailing: ' . $e->getMessage(), [
                'mailing_id' => $mailing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function scheduleRegularMailing($recipients, $name, $content): void
    {
        foreach ($recipients as $recipient) {
            if (!$recipient->birth_date) {
                Log::warning('Recipient has no birthday set', ['recipient_id' => $recipient->id]);
                continue;
            }

            $birthdayParts = explode('-', $recipient->birth_date);
            $month = (int)$birthdayParts[1];
            $day = (int)explode(' ', $birthdayParts[2])[0];

            $birthdayThisYear = Carbon::createFromDate(
                Carbon::now()->year,
                $month,
                $day
            );

            for ($i = Mailing::DAYS_BEFORE_BIRTHDAY; $i >= 0; $i--) {
                $scheduledAt = $birthdayThisYear->copy()->subDays($i);
                $scheduledAt->setTimeFromTimeString(Mailing::SEND_TIME);

                $clientMailing = Mailing::query()->create([
                    'name' => $name,
                    'content' => $content,
                    'status' => MailingStatusEnum::SCHEDULED,
                    'total_recipients' => 1,
                    'scheduled_at' => $scheduledAt
                ]);

                $clientMailing->recipients()->attach([
                    $recipient->id => ['status' => MailingRecipientStatusEnum::PENDING]
                ]);

                SendMailingJob::dispatch($clientMailing)
                    ->delay($scheduledAt)
                    ->onQueue('mailings');
            }
        }
    }

    private function getRecipients(array $validated): Collection|array
    {
        $query = Client::query();

        if (!($validated['send_to_all'] ?? true)) {
            $query->whereIn('id', $validated['client_ids'] ?? []);
        }

        return $query->get();
    }

    private function determineMailingStatus(array $validated): string
    {
        return in_array($validated['send_type'], [MailingSendTypeEnum::NOW, MailingSendTypeEnum::REGULAR]) ||
        ($validated['send_type'] === MailingSendTypeEnum::AUTO && !empty($validated['scheduled_at']))
            ? MailingStatusEnum::SCHEDULED
            : MailingStatusEnum::DRAFT;
    }
}
