<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\PropertyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HR\DutyRosterController;
use App\Http\Controllers\PMS\AmenityController;
use App\Http\Controllers\PMS\HousekeepingController;
use App\Http\Controllers\PMS\POSController;
use App\Http\Controllers\PMS\POSItemController;
use App\Http\Controllers\PMS\RateTypeController;
use App\Http\Controllers\PMS\ReservationController;
use App\Http\Controllers\PMS\RoomController;
use App\Http\Controllers\PMS\RoomTypeController;
use App\Http\Controllers\PMS\TaxController;
use App\Http\Controllers\PMS\UserController;
use App\Http\Controllers\SuperAdmin\ModuleController;
use App\Http\Controllers\SuperAdmin\PropertyModuleController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\ShiftController;


use App\Http\Controllers\SuperAdmin\RoleController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['jwt.custom'])->group(function () {
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::middleware('role:super-admin')->group(function () {
        // Route::apiResource('property', PropertyController::class);
        Route::get('property', [PropertyController::class, 'index']);
        Route::post('property', [PropertyController::class, 'store']);
        Route::delete('property/{id}', [PropertyController::class, 'destroy']);
        Route::put('property/temporaryDisable/{id}', [PropertyController::class, 'temporaryDisable']);
        Route::apiResource('modules', ModuleController::class);
        Route::get('properties/{id}/modules', [PropertyModuleController::class, 'index']);
        Route::post('properties/{id}/modules', [PropertyModuleController::class, 'assign']);
        Route::delete('properties/{id}/modules', [PropertyModuleController::class, 'remove']);
        Route::patch('properties/{id}/modules/toggle', [PropertyModuleController::class, 'toggle']);
    });
    Route::middleware('role:admin,super-admin')->group(function () {
        Route::get('property/{id}', [PropertyController::class, 'show']);
        Route::put('property/{id}', [PropertyController::class, 'update']);
    });
});


Route::prefix('pms')->middleware(['jwt.custom', 'role:admin', 'property.inject', 'module.access:pms'])->group(function () {
    Route::post('reservations/{id}', [ReservationController::class, 'update']);
    Route::apiResource('rate-types', RateTypeController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('rooms', RoomController::class);
    Route::apiResource('amenities', AmenityController::class);
    Route::apiResource('room-type', RoomTypeController::class);
    Route::apiResource('reservations', ReservationController::class);
    Route::apiResource('pos', POSController::class);
    Route::apiResource('housekeeping', HousekeepingController::class);
    Route::apiResource('tax', TaxController::class);
    Route::apiResource('pos/items', POSItemController::class);
});

Route::middleware(['jwt.custom', 'role:admin', 'property.inject', 'module.access:pms'])->group(function () {
    Route::apiResource('roles', RoleController::class);
});


Route::prefix('pms')->middleware(['jwt.custom', 'role:frontdesk', 'property.inject', 'module.access:pms'])->group(function () {
    Route::apiResource('reservations', ReservationController::class);
});

//HRMS Routes
Route::prefix('hrms')->middleware(['jwt.custom', 'role:hr,admin', 'property.inject', 'module.access:hrms',])->group(function () {

    //employee management routes
        Route::get('employees', [EmployeeController::class, 'index']);
        Route::get('employees/sample/download', [EmployeeController::class, 'downloadSample']);
        Route::post('employees', [EmployeeController::class, 'store']);
        Route::post('employees/upload', [EmployeeController::class, 'upload']);
        Route::put('employees/{id}', [EmployeeController::class, 'update']);
        Route::delete('employees/{id}', [EmployeeController::class, 'destroy']);
        Route::get('employees/{id}', [EmployeeController::class, 'show']);

    //shift management routes
        Route::get('shifts', [ShiftController::class, 'index']);
        Route::get('shifts/{id}', [ShiftController::class, 'show']);
        Route::post('shifts', [ShiftController::class, 'store']);
        Route::put('shifts/{id}', [ShiftController::class, 'update']);
        Route::delete('shifts/{id}', [ShiftController::class, 'destroy']);

    //duty roster routes
        Route::get('rosters', [DutyRosterController::class, 'index']); // weekly view: ?week_start=YYYY-MM-DD
        Route::get('rosters/sample', [DutyRosterController::class, 'sample']);
        Route::post('rosters/upload', [DutyRosterController::class, 'upload']);
        Route::post('rosters', [DutyRosterController::class, 'store']);
        Route::get('rosters/{id}', [DutyRosterController::class, 'show']);
        Route::put('rosters/{id}', [DutyRosterController::class, 'update']);
        Route::delete('rosters/{id}', [DutyRosterController::class, 'destroy']);
});
