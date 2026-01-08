<?php

use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated. Please use the API login endpoint.'], 401);
})->name('login');

