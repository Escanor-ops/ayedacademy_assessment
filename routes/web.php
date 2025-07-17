<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Employee\HomeController as EmployeeHomeController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Mission\MissionController;
use App\Http\Controllers\Mission\MissionReportController;
use App\Http\Controllers\Mission\MissionTypeController;
//super
use App\Http\Controllers\SuperManager\DepartmentController;
use App\Http\Controllers\SuperManager\StandardController;
use App\Http\Controllers\SuperManager\ManagerController;
use App\Http\Controllers\SuperManager\DepartmentManagerController;
use App\Http\Controllers\SuperManager\EmployeeController;
use App\Http\Controllers\SuperManager\HomeController as SuperManagerHomeController;

//manager
use App\Http\Controllers\Manager\StandardController as ManagerStandardController;
use App\Http\Controllers\Manager\HomeController as ManagerHomeController;
use App\Http\Controllers\Manager\EvaluationController as ManagerEvaluationController;
use App\Http\Controllers\Manager\DepartmentController as ManagerDepartmentControllr;




//department
use App\Http\Controllers\DepartmentManager\StandardController as DepartmentStandardController;
use App\Http\Controllers\DepartmentManager\HomeController as DepartmentManagerHomeController;
use App\Http\Controllers\DepartmentManager\EvaluationController as DepartmentEvaluationController;



// employee
use App\Http\Controllers\Employee\EvaluationController as EmployeeEvaluationController;

// Authentication Routes
Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/', [LoginController::class, 'login'])->name('attempt_login');

// Password Reset Routes
Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');
Route::get('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])
    ->name('password.reset');
Route::post('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])
    ->name('password.update');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::post('/copy-standards',[StandardController::class,'copy'])->name('standards.copy');
    Route::get('missions', [MissionController::class, 'index'])->name('missions.index');
    Route::get('department-mission/{id}', [MissionController::class, 'viewMissions'])->name('department.missions');
    Route::post('mission/add', [MissionController::class, 'store'])->name('mission.add');
    Route::post('mission/update', [MissionController::class, 'updateStatus'])->name('mission.updateStatus');

    // Mission Types Management Routes
    Route::post('mission-types', [MissionTypeController::class, 'store'])->name('mission-types.store');
    Route::match(['post', 'patch'], 'mission-types/{missionType}/toggle-status', [MissionTypeController::class, 'toggleStatus'])->name('mission-types.toggle-status');
    Route::get('mission-types/{department}/list', [MissionTypeController::class, 'list'])->name('mission-types.list');

    Route::get('missions/{mission}/reports', [MissionReportController::class, 'showReports'])->name('missions.reports');
    Route::post('missions/{mission}/reports', [MissionReportController::class, 'storeReport'])->name('missions.reports.store');
    Route::post('/missions/{id}/upload', [MissionController::class, 'uploadFiles'])->name('missions.file');
    Route::get('/missions/files/{file}/download', [MissionController::class, 'downloadFile'])->name('missions.file.download');
    Route::get('/department/{id}/missions/data', [MissionController::class, 'getMissionsData'])
        ->name('department.missions.data')
        ->middleware('auth');

    Route::get('/departments/data', [MissionController::class, 'getDepartmentsData'])->name('departments.data');

});

// Super Manager Routes
Route::middleware(['auth', RoleMiddleware::class . ':super_manager'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperManagerHomeController::class, 'index'])->name('home');
        Route::resource('departments', DepartmentController::class);
        Route::resource('standards', StandardController::class);
        Route::resource('managers', ManagerController::class);
        Route::resource('employees', EmployeeController::class);
        Route::resource('departments-managers', DepartmentManagerController::class);
        Route::post('departments-managers/{id}/transfer', [DepartmentManagerController::class, 'transfer'])->name('departments-managers.transfer');
    });




// Manager Routes
Route::middleware(['auth', RoleMiddleware::class . ':manager'])
    ->prefix('manager')
    ->as('manager.')
    ->group(function () {
        Route::get('/dashboard', [ManagerHomeController::class, 'index'])->name('dashboard');

        Route::resource('departments', ManagerDepartmentControllr::class);
        Route::resource('evaluation', \App\Http\Controllers\Manager\EvaluationController::class);
        Route::post('/evaluation/change-status', [\App\Http\Controllers\Manager\EvaluationController::class, 'changeStatus'])
        ->name('evaluation.changeStatus');
        Route::get('/evaluation/view/{department}/{month}', [\App\Http\Controllers\Manager\EvaluationController::class, 'view'])->name('evaluation.view');
        Route::get('/evaluation/emp/{employee}/{month}', [\App\Http\Controllers\Manager\EvaluationController::class, 'empEvaluation'])->name('employee.evaluation');

        Route::post('/evaluation/confirm/{department}', [ManagerDepartmentControllr::class, 'confirm'])
        ->name('evaluation.confirm');

        Route::get('standards/{id}', [ManagerStandardController::class, 'index'])->name('standards.index');
        Route::post('standards/store', [ManagerStandardController::class, 'store'])->name('standards.store');
        Route::post('standards/destroy/{id}', [ManagerStandardController::class, 'destroy'])->name('standards.destroy');
        Route::post('standards/update', [ManagerStandardController::class, 'update'])->name('standards.update');

    });





// Department Manager Routes
Route::middleware(['auth', RoleMiddleware::class . ':department_manager'])
    ->prefix('department_manager')
    ->as('department_manager.')
    ->group(function () {
        Route::get('/dashboard', [DepartmentManagerHomeController::class, 'index'])->name('dashboard');
        Route::resource('evaluation', DepartmentEvaluationController::class)->except(['show']);
        Route::post('/evaluation/change-status', [DepartmentEvaluationController::class, 'changeStatus'])
        ->name('evaluation.changeStatus');
        Route::get('/evaluation/showEvaluation', [DepartmentEvaluationController::class, 'showEvaluation'])->name('evaluation.showEvaluation');
        Route::get('/evaluation/details/{employee}/{month}', [DepartmentEvaluationController::class, 'details'])->name('evaluation.details');
        Route::post('evaluation/delete', [DepartmentEvaluationController::class, 'deleteEvaluations'])
            ->name('evaluation.delete');

        Route::get('standards/{id}', [DepartmentStandardController::class, 'index'])->name('standards.index');
        Route::post('standards/store', [DepartmentStandardController::class, 'store'])->name('standards.store');
        Route::post('standards/destroy/{id}', [DepartmentStandardController::class, 'destroy'])->name('standards.destroy');
        Route::post('standards/update', [DepartmentStandardController::class, 'update'])->name('standards.update');
        Route::post('standards/copy', [DepartmentStandardController::class, 'copy'])->name('standards.copy');
    });

// Employee Routes
Route::middleware(['auth', RoleMiddleware::class . ':employee'])
    ->prefix('employee')
    ->as('employee.')
    ->group(function () {
        Route::get('/home', [EmployeeHomeController::class, 'home'])->name('home');

        Route::get('/evaluation', [EmployeeEvaluationController::class, 'showEvaluation'])->name('evaluation.showEvaluation');
        Route::get('/evaluation/details/{id}', [EmployeeEvaluationController::class, 'details'])->name('evaluation.details');
    });

// Profile routes accessible to all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update-password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.update-password');
});


