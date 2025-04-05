<?php

namespace App\Http\Controllers\Api\EmailSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailSettingRequest;
use App\Http\Resources\EmailSettingResource;
use App\Models\EmailSetting;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;

class EmailSettingUpdateController extends Controller
{
    public function __invoke(EmailSettingRequest $request, EmailSetting $emailSetting): JsonResponse
    {
        $emailSetting->update($request->validated());

        if ($emailSetting->is_active) {
            EmailService::updateMailConfig($emailSetting);
        }

        return response()->json([
            'message' => 'Настройки почты обновлены',
            'data' => new EmailSettingResource($emailSetting)
        ]);
    }
}
