<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdmissionApplicationController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\TeacherPortalController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\AttendanceReportController;

Route::get('/', function () {
    $schoolName = \App\Models\SchoolSetting::query()->value('name') ?? 'Madrasaa Ifaa Islaamaa';

    return view('welcome', ['schoolName' => $schoolName]);
});
Route::get('/admin/attendance-report.csv', [AttendanceReportController::class, 'downloadCsv'])->middleware('auth:web')->name('admin.attendance-report');
Route::middleware('auth:web')->group(function (): void {
    Route::get('/attendance/reports', [AttendanceReportController::class, 'show'])->name('attendance.reports.show');
    Route::get('/attendance/reports/export/{format}', [AttendanceReportController::class, 'download'])->name('attendance.reports.download');
});

Route::get('/admissions/apply', [AdmissionApplicationController::class, 'create'])->name('admissions.create');
Route::post('/admissions/apply', [AdmissionApplicationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('admissions.store');
Route::get('/admissions/submitted/{reference}', [AdmissionApplicationController::class, 'submitted'])
    ->name('admissions.submitted');

Route::get('/student/login', [StudentPortalController::class, 'login'])->name('student.login');
Route::redirect('/login', '/student/login')->name('login');
Route::post('/student/login', [StudentPortalController::class, 'authenticate'])
    ->middleware('throttle:5,1')
    ->name('student.authenticate');
Route::middleware('auth:web')->prefix('student')->name('student.')->group(function (): void {
    Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [StudentPortalController::class, 'attendance'])->name('attendance');
    Route::get('/results/{result}', [StudentPortalController::class, 'reportCard'])->name('results.show');
    Route::get('/password', [StudentPortalController::class, 'editPassword'])->name('password.edit');
    Route::post('/password', [StudentPortalController::class, 'updatePassword'])->name('password.update');
    Route::post('/logout', [StudentPortalController::class, 'logout'])->name('logout');
});

Route::get('/parent/login', [ParentPortalController::class, 'login'])->name('parent.login');
Route::post('/parent/login', [ParentPortalController::class, 'authenticate'])->middleware('throttle:5,1')->name('parent.authenticate');
Route::middleware('auth:web')->prefix('parent')->name('parent.')->group(function (): void {
    Route::get('/dashboard', [ParentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/children/{student}', [ParentPortalController::class, 'child'])->name('children.show');
    Route::get('/children/{student}/results/{result}', [ParentPortalController::class, 'reportCard'])->name('results.show');
    Route::get('/password', [ParentPortalController::class, 'editPassword'])->name('password.edit');
    Route::post('/password', [ParentPortalController::class, 'updatePassword'])->name('password.update');
    Route::post('/logout', [ParentPortalController::class, 'logout'])->name('logout');
});

Route::get('/teacher/login', [TeacherPortalController::class, 'login'])->name('teacher.login');
Route::post('/teacher/login', [TeacherPortalController::class, 'authenticate'])
    ->middleware('throttle:5,1')
    ->name('teacher.authenticate');
Route::middleware('auth:web')->prefix('teacher')->name('teacher.')->group(function (): void {
    Route::get('/dashboard', [TeacherPortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/attendance', [TeacherPortalController::class, 'storeAttendance'])->middleware('throttle:60,1')->name('attendance.store');
    Route::post('/marks', [TeacherPortalController::class, 'storeMark'])->middleware('throttle:60,1')->name('marks.store');
    Route::post('/exam-subjects/{examSubject}/assessment-components', [TeacherPortalController::class, 'saveAssessmentComponents'])->middleware('throttle:30,1')->name('assessment-components.save');
    Route::post('/exam-subjects/{examSubject}/marks/submit', [TeacherPortalController::class, 'submitAssessmentMarks'])->middleware('throttle:20,1')->name('assessment-marks.submit');
    Route::post('/logout', [TeacherPortalController::class, 'logout'])->name('logout');
});
