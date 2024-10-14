<?php

use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\Worker\WorkerAuthContoller;

Route::controller(WorkerAuthContoller::class)->group([
    
    'prefix' => 'worker/auth' , 
    'middleware' => 'DbBackup'
], function () {
    Route::post('/login', 'login');
    Route::post('/register',   'register');
    Route::post('/logout',   'logout');
    Route::post('/refresh',   'refresh');
    Route::get('/user-profile',   'userProfile');
});
