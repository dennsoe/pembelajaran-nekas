<?php

use App\Helpers;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ImportExportController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ScheduleController as TeacherScheduleController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\QrCodeController as StudentQrCodeController;
use App\Http\Controllers\Kurikulum\AttendanceValidationController;
use App\Http\Controllers\Kurikulum\ScheduleManagementController;
use App\Http\Controllers\Kurikulum\LeaveRequestController;
use App\Http\Controllers\KepalaSekolah\DashboardController as KepalaSekolahDashboardController;
use App\Http\Controllers\KepalaSekolah\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/login');
});

// Google OAuth Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Root redirect based on user group
    Route::get('/', function () {
        $user = Auth::user();
        return match ($user->group) {
            'superadmin', 'admin' => redirect('/admin/dashboard'),
            'kepala_sekolah' => redirect('/kepala-sekolah/dashboard'),
            'kurikulum' => redirect('/kurikulum/dashboard'),
            'guru' => redirect('/guru/dashboard'),
            default => redirect('/home'),
        };
    });

    // GURU AREA (Teachers)
    Route::prefix('guru')->middleware('guru')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('guru.dashboard');
        
        // Schedule Management
        Route::get('/jadwal', [TeacherScheduleController::class, 'index'])->name('guru.jadwal');
        Route::get('/jadwal/{schedule}', [TeacherScheduleController::class, 'show'])->name('guru.jadwal.show');
        
        // Attendance
        Route::get('/absensi', [TeacherAttendanceController::class, 'index'])->name('guru.absensi');
        Route::post('/absensi/check-in', [TeacherAttendanceController::class, 'checkIn'])->name('guru.absensi.checkin');
        Route::post('/absensi/check-out', [TeacherAttendanceController::class, 'checkOut'])->name('guru.absensi.checkout');
        Route::get('/absensi/history', [TeacherAttendanceController::class, 'history'])->name('guru.absensi.history');
        
        // Leave Request
        Route::get('/izin', [TeacherAttendanceController::class, 'leaveRequest'])->name('guru.izin');
        Route::post('/izin', [TeacherAttendanceController::class, 'storeLeaveRequest'])->name('guru.izin.store');
        
        // Substitute Teacher
        Route::get('/guru-pengganti', [TeacherScheduleController::class, 'substitute'])->name('guru.pengganti');
    });

    // STUDENT AREA
    Route::prefix('siswa')->middleware('auth:student')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('siswa.dashboard');
        Route::get('/qr-code', [StudentQrCodeController::class, 'show'])->name('siswa.qrcode');
        Route::get('/jadwal', [StudentQrCodeController::class, 'schedule'])->name('siswa.jadwal');
    });

    // KURIKULUM AREA (Curriculum Staff)
    Route::prefix('kurikulum')->middleware('kurikulum')->group(function () {
        Route::get('/dashboard', function () {
            return view('kurikulum.dashboard');
        })->name('kurikulum.dashboard');
        
        // Attendance Validation
        Route::get('/validasi-absensi', [AttendanceValidationController::class, 'index'])->name('kurikulum.validasi');
        Route::post('/validasi-absensi/{attendance}/approve', [AttendanceValidationController::class, 'approve'])->name('kurikulum.validasi.approve');
        Route::post('/validasi-absensi/{attendance}/reject', [AttendanceValidationController::class, 'reject'])->name('kurikulum.validasi.reject');
        
        // Schedule Management
        Route::get('/jadwal', [ScheduleManagementController::class, 'index'])->name('kurikulum.jadwal');
        Route::get('/jadwal/create', [ScheduleManagementController::class, 'create'])->name('kurikulum.jadwal.create');
        Route::post('/jadwal', [ScheduleManagementController::class, 'store'])->name('kurikulum.jadwal.store');
        Route::get('/jadwal/{schedule}/edit', [ScheduleManagementController::class, 'edit'])->name('kurikulum.jadwal.edit');
        Route::put('/jadwal/{schedule}', [ScheduleManagementController::class, 'update'])->name('kurikulum.jadwal.update');
        Route::delete('/jadwal/{schedule}', [ScheduleManagementController::class, 'destroy'])->name('kurikulum.jadwal.destroy');
        
        // Leave Request Management
        Route::get('/izin', [LeaveRequestController::class, 'index'])->name('kurikulum.izin');
        Route::post('/izin/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('kurikulum.izin.approve');
        Route::post('/izin/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('kurikulum.izin.reject');
    });

    // KEPALA SEKOLAH AREA (Principal)
    Route::prefix('kepala-sekolah')->middleware('kepala_sekolah')->group(function () {
        Route::get('/dashboard', [KepalaSekolahDashboardController::class, 'index'])->name('kepala-sekolah.dashboard');
        
        // Reports
        Route::get('/laporan', [ReportController::class, 'index'])->name('kepala-sekolah.laporan');
        Route::get('/laporan/absensi-guru', [ReportController::class, 'teacherAttendance'])->name('kepala-sekolah.laporan.guru');
        Route::get('/laporan/kehadiran-pembelajaran', [ReportController::class, 'lessonAttendance'])->name('kepala-sekolah.laporan.pembelajaran');
        Route::get('/laporan/export', [ReportController::class, 'export'])->name('kepala-sekolah.laporan.export');
        
        // Attendance Validation (same as kurikulum)
        Route::get('/validasi-absensi', [AttendanceValidationController::class, 'index'])->name('kepala-sekolah.validasi');
        Route::post('/validasi-absensi/{attendance}/approve', [AttendanceValidationController::class, 'approve'])->name('kepala-sekolah.validasi.approve');
        Route::post('/validasi-absensi/{attendance}/reject', [AttendanceValidationController::class, 'reject'])->name('kepala-sekolah.validasi.reject');
        
        // Leave Request Approval
        Route::get('/izin', [LeaveRequestController::class, 'index'])->name('kepala-sekolah.izin');
        Route::post('/izin/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('kepala-sekolah.izin.approve');
        Route::post('/izin/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('kepala-sekolah.izin.reject');
    });

    // OLD USER AREA (kept for backward compatibility)
    Route::middleware('user')->group(function () {
        Route::get('/home', HomeController::class)->name('home');

        Route::get('/apply-leave', [UserAttendanceController::class, 'applyLeave'])
            ->name('apply-leave');
        Route::post('/apply-leave', [UserAttendanceController::class, 'storeLeaveRequest'])
            ->name('store-leave-request');

        Route::get('/attendance-history', [UserAttendanceController::class, 'history'])
            ->name('attendance-history');
    });

    // ADMIN AREA (Superadmin & Admin)
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/', fn () => redirect('/admin/dashboard'));
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Barcode
        Route::resource('/barcodes', BarcodeController::class)
            ->only(['index', 'show', 'create', 'store', 'edit', 'update'])
            ->names([
                'index' => 'admin.barcodes',
                'show' => 'admin.barcodes.show',
                'create' => 'admin.barcodes.create',
                'store' => 'admin.barcodes.store',
                'edit' => 'admin.barcodes.edit',
                'update' => 'admin.barcodes.update',
            ]);
        Route::get('/barcodes/download/all', [BarcodeController::class, 'downloadAll'])
            ->name('admin.barcodes.downloadall');
        Route::get('/barcodes/{id}/download', [BarcodeController::class, 'download'])
            ->name('admin.barcodes.download');

        // User/Employee/Karyawan
        Route::resource('/employees', EmployeeController::class)
            ->only(['index'])
            ->names(['index' => 'admin.employees']);

        // Master Data
        Route::get('/masterdata/division', [MasterDataController::class, 'division'])
            ->name('admin.masters.division');
        Route::get('/masterdata/job-title', [MasterDataController::class, 'jobTitle'])
            ->name('admin.masters.job-title');
        Route::get('/masterdata/education', [MasterDataController::class, 'education'])
            ->name('admin.masters.education');
        Route::get('/masterdata/shift', [MasterDataController::class, 'shift'])
            ->name('admin.masters.shift');
        Route::get('/masterdata/admin', [MasterDataController::class, 'admin'])
            ->name('admin.masters.admin');

        // New Master Data for Teacher System
        Route::get('/masterdata/subjects', [MasterDataController::class, 'subjects'])
            ->name('admin.masters.subjects');
        Route::get('/masterdata/classrooms', [MasterDataController::class, 'classrooms'])
            ->name('admin.masters.classrooms');
        Route::get('/masterdata/academic-years', [MasterDataController::class, 'academicYears'])
            ->name('admin.masters.academic-years');
        Route::get('/masterdata/lesson-periods', [MasterDataController::class, 'lessonPeriods'])
            ->name('admin.masters.lesson-periods');

        // Presence/Absensi
        Route::get('/attendances', [AttendanceController::class, 'index'])
            ->name('admin.attendances');

        // Presence/Absensi
        Route::get('/attendances/report', [AttendanceController::class, 'report'])
            ->name('admin.attendances.report');

        // Import/Export
        Route::get('/import-export/users', [ImportExportController::class, 'users'])
            ->name('admin.import-export.users');
        Route::get('/import-export/attendances', [ImportExportController::class, 'attendances'])
            ->name('admin.import-export.attendances');

        Route::post('/users/import', [ImportExportController::class, 'importUsers'])
            ->name('admin.users.import');
        Route::post('/attendances/import', [ImportExportController::class, 'importAttendances'])
            ->name('admin.attendances.import');

        Route::get('/users/export', [ImportExportController::class, 'exportUsers'])
            ->name('admin.users.export');
        Route::get('/attendances/export', [ImportExportController::class, 'exportAttendances'])
            ->name('admin.attendances.export');
    });
});

Livewire::setUpdateRoute(function ($handle) {
    return Route::post(Helpers::getNonRootBaseUrlPath() . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    $path = config('app.debug') ? '/livewire/livewire.js' : '/livewire/livewire.min.js';
    return Route::get(url($path), $handle);
});
