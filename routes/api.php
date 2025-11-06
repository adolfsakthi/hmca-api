<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\PropertyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PMS\AmenityController;
use App\Http\Controllers\PMS\ReservationController;
use App\Http\Controllers\PMS\RoomController;
use App\Http\Controllers\PMS\RoomTypeController;
use App\Http\Controllers\PMS\UserController;
use App\Http\Controllers\SuperAdmin\ModuleController;
use App\Http\Controllers\SuperAdmin\PropertyModuleController;
use App\Models\PMS\RoomType;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['jwt.custom'])->group(function () {
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('property', PropertyController::class);
        Route::put('property/temporaryDisable/{id}', [PropertyController::class, 'temporaryDisable']);
        Route::apiResource('modules', ModuleController::class);
        Route::get('properties/{id}/modules', [PropertyModuleController::class, 'index']);
        Route::post('properties/{id}/modules', [PropertyModuleController::class, 'assign']);
        Route::delete('properties/{id}/modules', [PropertyModuleController::class, 'remove']);
        Route::patch('properties/{id}/modules/toggle', [PropertyModuleController::class, 'toggle']);
    });
});


Route::prefix('pms')->middleware(['jwt.custom', 'role:property_admin', 'property.inject', 'module.access:pms'])->group(function () {
    Route::post('reservations/{id}', [ReservationController::class, 'update']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('rooms', RoomController::class);
    Route::apiResource('amenities', AmenityController::class);
    Route::apiResource('room-type', RoomTypeController::class);
    Route::apiResource('reservations', ReservationController::class);
});
