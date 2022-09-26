<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('hello');
// });

// Route::get('/contacts', function($id){
//     return view('contacts',['id'=>$id]);
// });

//shorthand routing
// Route::view('contacts','contacts'); 
Route::view('hello','hello'); 
Route::view('about','about'); 
Route::view('/','welcome'); 

//the redirect method
// 

//routing a controller
// Route::get("display/{id}", [Users::class, 'index']);


// Route::get('/contacts/{id}', function($id){
//         return view('contacts', ['id'=> $id]);
// });

// Route::get('users', [UsersController::class, 'loadView']);
// Route::get('users', [UsersController::class, 'products']);

Route::view('login','users');
Route::post('user', [UsersController::class, 'process']);
