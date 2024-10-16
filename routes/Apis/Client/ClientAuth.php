<?php

use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\Client\ClientAuthController;


Route::prefix('admin/auth')->middleware('DbBackup')->controller(ClientAuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::get('/user-profile', 'userProfile');
});
