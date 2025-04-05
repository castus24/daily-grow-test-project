<?php

namespace App\Http\Controllers\Api\EmailSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailSettingRequest;
use App\Http\Resources\EmailSettingResource;
use App\Models\EmailSetting;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EmailSettingAddController extends Controller
{
    public function __invoke(EmailSettingRequest $request): JsonResponse
    {
        EmailSetting::query()
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $setting = EmailSetting::query()->create($request->validated());

        /** @var EmailSetting $setting */
        EmailService::updateMailConfig($setting);

        return response()->json([
            'message' => 'Почта добавлена',
            'data' => new EmailSettingResource($setting)
            ], ResponseAlias::HTTP_CREATED);
    }
}
