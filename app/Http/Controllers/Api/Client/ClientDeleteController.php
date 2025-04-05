<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;

class ClientDeleteController extends Controller
{
    public function __invoke(Client $client): JsonResponse
    {
        $client->delete();

        return response()->json([
            'message' => 'Клиент удален'
        ]);
    }
}
