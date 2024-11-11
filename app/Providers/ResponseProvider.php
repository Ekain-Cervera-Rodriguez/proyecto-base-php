<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ServiceProvider;

class ResponseProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Response::macro('success', function ($data) {
            return Response::json($data, 200);
        });

        Response::macro('badRequest', function ($message) {
            return Response::json(['error' => $message], 400);
        });

        Response::macro('unauthorized', function ($reason) {
            return Response::json(['error' => $reason], 401);
        });

        Response::macro('forbidden', function ($reason) {
            return Response::json(['error' => $reason], 403);
        });

        Response::macro('notFound', function ($reason) {
            return Response::json(['error' => $reason], 404);
        });

        Response::macro('unprocessable', function ($message, $errors = []) {

            if ($errors instanceof MessageBag) {

                $err = [];
                foreach ($errors->toArray() as $error) {
                    $err[] = $error[0];
                }
                $errors = $err;
            }

            Log::info("Error - Mensaje: $message - Errors: " . json_encode($errors));

            return Response::json(['message' => $message, 'errors' => $errors], 422);
        });

        Response::macro('tooManyRequests', function ($reason) {
            return Response::json(['error' => $reason], 429);
        });
    }
}
