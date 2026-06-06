<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(
            PersonalAccessToken::class
        );

        Response::macro('api', function ($data = null, string $message = 'Success', int $code = 200, array $extra = []) {
            $response = ['success' => true, 'message' => $message, 'data' => $data];

            return response()->json(array_merge($response, $extra), $code);
        });
    }
}
