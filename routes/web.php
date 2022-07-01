<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\SchedulesController;
use App\Http\Controllers\SpeakersController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\RestoController;


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
//     return view('welcome');
// });

Route::get('/',[HomeController::class, 'index']);
Route::get('/about',[AboutController::class, 'index']);
Route::get('/contact',[ContactController::class, 'index']);
Route::get('/pricing',[PricingController::class, 'index']);
Route::get('/schedule',[SchedulesController::class, 'index']);
Route::get('/speaker',[SpeakersController::class, 'index']);
Route::get('/venue',[VenueController::class, 'index']);

Route::view('register', 'admin.register')->middleware('customAuth');
Route::view('login', 'admin.login')->middleware('customAuth');

Route::post('registerUser', [RestoController::class, 'registerUser']);
Route::post('loginUser', [RestoController::class, 'login']);
Route::get('dashboard', [RestoController::class, 'index']);
Route::get('logout', [RestoController::class, 'logout']);

// Route::group(['middleware' => 'customAuth'], function () {
//     Route::get('/list', 'RestoController@list');
//     Route::view('/add', 'add');
//     Route::post('addResto', 'RestoController@addResto');
//     Route::view('register', 'register');
//     Route::view('login', 'login');
//     Route::get('logout', 'RestoController@logout');
// });
