<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @method createToken(string $name, array $abilities = ['*'])
 */
class LoginResource extends JsonResource
{
    public string $token_type = "Bearer";

    public function toArray(Request $request): array
    {
        return [
            'token_type' => $this->token_type,
            'token' => $this->createToken(config('app.name'))->plainTextToken,
        ];
    }
}
