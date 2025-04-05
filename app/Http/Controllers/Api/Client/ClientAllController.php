<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\QueryBuilder;

class ClientAllController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->input('per_page', 10);

        return ClientResource::collection(
            QueryBuilder::for(Client::class)
                ->allowedFilters(Client::getAllowedFilters())
                ->allowedSorts(Client::getAllowedSorts())
                ->paginate($request->input('itemsPerPage', $perPage))
        );
    }
}
