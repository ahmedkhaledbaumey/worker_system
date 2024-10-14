<?php

use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\Admin\AdminAuthContoller;

Route::controller(AdminAuthContoller::class)->group([
    
    'prefix' => 'admin/auth' , 
    'middleware' => 'DbBackup'
], function () {
    Route::post('/login', 'login');
    Route::post('/register',   'register');
    Route::post('/logout',   'logout');
    Route::post('/refresh',   'refresh');
    Route::get('/user-profile',   'userProfile');
});
