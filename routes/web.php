<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Route::get('login', \App\Livewire\Auth\Login::class)->name('login');
Route::get('logout', function (){
    auth()->logout();
    return redirect('login');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin','throttle:100,5']], function () {
    include_route_files(__DIR__.'/backend/');
});
