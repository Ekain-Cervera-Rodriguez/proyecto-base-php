<?php

use App\Http\Controllers\Web\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('ping',function(){
    return response()->json(['result' => 'ok']);
});


Route::get('usuarios/roles', [UsersController::class, 'roles'])->name('usuarios.roles');
Route::resource('usuarios', UsersController::class, ['except' => ['edit', 'create']]);
