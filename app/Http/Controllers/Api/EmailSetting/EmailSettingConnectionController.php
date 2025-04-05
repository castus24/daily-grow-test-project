<?php

namespace App\Http\Controllers\Api\EmailSetting;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmailSettingResource;
use App\Mail\TestConnectionEmail;
use App\Models\EmailSetting;
use App\Services\EmailService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EmailSettingConnectionController extends Controller
{
    public function testConnection(EmailSetting $emailSetting): JsonResponse
    {
        try {
            EmailService::updateMailConfig($emailSetting);

            Mail::to($emailSetting->mail_from_address)
                ->send(new TestConnectionEmail());

            return response()->json([
                'message' => 'Соединение установлено'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function activate(EmailSetting $emailSetting): EmailSettingResource
    {
        EmailSetting::query()
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $emailSetting->update(['is_active' => true]);

        EmailService::updateMailConfig($emailSetting);

        return new EmailSettingResource($emailSetting);
    }
}
