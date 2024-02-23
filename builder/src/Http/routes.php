<?php

use System\Builder\Http\Controllers;
use Illuminate\Support\Facades\Route;
use System\Builder\Http\Middleware\BuilderInit;

Route::get('builder/add', Controllers\BuilderController::class . '@add');
Route::post('builder', Controllers\BuilderController::class . '@store');
