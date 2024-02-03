<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;


Route::post('register', [AuthController::class,'register'])->name('register');
Route::post('/login', [AuthController::class,'login'])->name('login');
Route::middleware(['auth:api'])->group(function ()
{
Route::post('/departments', [EmployeeController::class, 'createDepartment']);
Route::post('/leaves', [EmployeeController::class, 'createLeave']);

Route::get('/get-user-details', [EmployeeController::class, 'getUserDetails']);



});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
