<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\AuthController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('expenses', ExpenseController::class);
    Route::post('/expenses/bulk', [ExpenseController::class, 'bulkStore']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/sync/expenses', [SyncController::class, 'getExpenses']);
    Route::post('/sync/expenses', [SyncController::class, 'syncExpenses']);
    Route::post('/sync/replace-all-client-data', [SyncController::class, 'replaceAllClientData']);
    Route::get('/sync/get-all-server-data', [SyncController::class, 'getAllServerData']);
});


Route::group([
    'prefix' => 'auth'
], function () {
    // These routes do not require authentication
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // These routes require authentication
    Route::middleware(['auth:api'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
});