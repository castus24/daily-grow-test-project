<?php

namespace App\Http\Controllers\Api\EmailSetting;

use App\Http\Resources\EmailSettingResource;
use App\Models\EmailSetting;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class EmailSettingAllController
{
    public function __invoke(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $defaultConfigs = EmailService::getDefaultConfigs();

        $settings = EmailSettingResource::collection(
            QueryBuilder::for(EmailSetting::class)
                ->allowedSorts(EmailSetting::getAllowedSorts())
                ->paginate($request->input('itemsPerPage', $perPage))
        );

        return response()->json([
            'settings' => $settings,
            'defaultConfigs' => $defaultConfigs
        ]);
    }
}
