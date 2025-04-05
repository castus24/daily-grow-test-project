<?php

namespace App\Http\Controllers\Api\EmailSetting;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use Illuminate\Http\JsonResponse;

class EmailSettingDeleteController extends Controller
{
    public function __invoke(EmailSetting $emailSetting): JsonResponse
    {
        $emailSetting->delete();

        return response()->json([
            'message' => 'Настройки почты удален'
        ]);
    }
}
