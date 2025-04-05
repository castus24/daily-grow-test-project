<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportClientsRequest;
use App\Imports\ClientsImport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{
    public function import(ImportClientsRequest $request): JsonResponse
    {
        $import = new ClientsImport(
            $request->boolean('update_existing'),
            $request->boolean('skip_duplicates')
        );

        Excel::import($import, $request->file('file'));

        return response()->json([
            'success' => true,
            'message' => 'Импорт завершен',
            'stats' => [
                'created' => $import->getCreatedCount(),
                'updated' => $import->getUpdatedCount(),
                'skipped' => $import->getSkippedCount(),
            ]
        ]);
    }
}
