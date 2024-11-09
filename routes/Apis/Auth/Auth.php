<?php 

use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\Auth\AuthController;

Route::prefix('auth')->middleware('DbBackup')->controller(AuthController::class)->group(function () {
    Route::post('/login/{guard}', 'login');
    Route::post('/register/{guard}', 'register');
    Route::post('/logout/{guard}', 'logout');
    Route::post('/refresh/{guard}', 'refresh');
    Route::get('/user-profile/{guard}', 'userProfile');
    Route::get('/verifiy/{guard}/{token}', 'verified');  // مسار التحقق
});
