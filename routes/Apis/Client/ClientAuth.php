<?php

use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\Client\ClientAuthContoller;

Route::controller(ClientAuthContoller::class)->group([
    
    'prefix' => 'client/auth' , 
    'middleware' => 'DbBackup'
], function () {
    Route::post('/login', 'login');
    Route::post('/register',   'register');
    Route::post('/logout',   'logout');
    Route::post('/refresh',   'refresh');
    Route::get('/user-profile',   'userProfile');
});
