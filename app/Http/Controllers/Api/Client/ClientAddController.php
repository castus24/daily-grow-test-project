<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ClientAddController extends Controller
{
    public function __invoke(ClientRequest $request): JsonResponse
    {
        $client = Client::query()->create($request->validated());

        return response()->json([
            'message' => 'Клиент добавлен',
            'data' => new ClientResource($client)
            ], ResponseAlias::HTTP_CREATED);
    }
}
