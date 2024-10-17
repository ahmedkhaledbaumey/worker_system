<?php

use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\Worker\WorkerAuthController;


Route::prefix('worker/auth')->middleware('DbBackup')->controller(WorkerAuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
    Route::get('/user-profile', 'userProfile');
}); 



// use App\Http\Controllers\{WorkerAuthController , AdminAuthController ,ClientAuthController };

// Route::prefix('auth/."$guard"')->middleware('DbBackup')->controller($guard.'AthhController'::class)->group(function () {
//     Route::post('/login', 'login');
//     Route::post('/register', 'register');
//     Route::post('/logout', 'logout');
//     Route::post('/refresh', 'refresh');
//     Route::get('/user-profile', 'userProfile');
// });   
//هنا انت محتاج تقلل كتابت الراوتس فممكن تعمل كدا لو ينفع بس هيفضل كل الكنترولات موجوده وكل الفانكشن جواها وتحدد الجارد بايدك ثابت جوا الفانكشن 
// مش متجربه 





// Route::prefix('auth')->middleware('DbBackup')->controller(AthhController::class)->group(function () {
//     Route::post('/login', 'login');
//     Route::post('/register', 'register');
//     Route::post('/logout', 'logout');
//     Route::post('/refresh', 'refresh');
//     Route::get('/user-profile', 'userProfile');
// }); 
//كدا انت محتاج بتاع الفرونت يبعتلك متغير واحد هيبقي اسم الجارد هتستخدمه فالفانكشن من جوا وهيستعمل ال api دول فكل الجاردات كدا انت ثبتت الراوتس والفانكشن جوا كنترولر واحد 

//انت مجربها قبل كدا في فانكشن اللوجن بتاعت التخرج  

//routes => Apis =>Auth =>AuthController 
// App\Http\Controllers\Api\Auth\AuthController



