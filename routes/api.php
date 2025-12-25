<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\StudentController;
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

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/student/login', [AuthController::class, 'studentLogin']);

// Protected routes for teachers
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Schedule
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::get('/schedules/today', [ScheduleController::class, 'today']);
    Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
    
    // Attendance
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    
    // QR Code validation
    Route::post('/qr-code/validate', [AttendanceController::class, 'validateQrCode']);
    
    // Face recognition
    Route::post('/face/register', [AttendanceController::class, 'registerFace']);
    Route::post('/face/verify', [AttendanceController::class, 'verifyFace']);
});

// Protected routes for students
Route::middleware('auth:student')->prefix('student')->group(function () {
    Route::get('/profile', [StudentController::class, 'profile']);
    Route::get('/qr-code', [StudentController::class, 'qrCode']);
    Route::get('/schedule', [StudentController::class, 'schedule']);
    Route::get('/schedule/today', [StudentController::class, 'todaySchedule']);
});
