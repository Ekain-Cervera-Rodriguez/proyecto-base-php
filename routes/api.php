<?php

use App\Http\Controllers\Auth\AuthenticateController;
use App\Http\Middleware\SentinelACL;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthenticateController::class, 'getAccessToken']);
Route::get('refresh',[AuthenticateController::class,'refreshAccessToken']);



Route::middleware(['jwt.auth', SentinelACL::class])->group(function () {
    Route::prefix('web')
        ->name('web.')
        ->group(base_path('routes/web.php'));
});


