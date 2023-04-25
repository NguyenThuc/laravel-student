<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EducationalInstitutionController;
use App\Http\Controllers\EducationalStaffController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication
Route::get('/login', [AuthController::class, 'getLogin'])->name('login');
Route::post('/login', [AuthController::class, 'postLogin'])->middleware("throttle:5,60");
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', [AuthController::class, 'getForgotPassword']);
Route::post('/forgot-password', [AuthController::class, 'postForgotPassword']);
Route::get('/reset-password', [AuthController::class, 'getResetPassword'])->name('get-reset-password');
Route::post('/reset-password', [AuthController::class, 'postResetPassword']);
Route::get('/active-seller', [AuthController::class, 'getActiveSeller'])->name('active-seller');
Route::get('/create-password', [AuthController::class, 'getCreatePassword'])->name('get-create-password');
Route::post('/create-password', [AuthController::class, 'postCreatePassword']);
Route::get('/active-success', [AuthController::class, 'activeDone']);

Route::group(['middleware' => 'auth'], function() {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/change-password', [AuthController::class, 'getChangePassword'])->name('change_password');
    Route::post('/change-password', [AuthController::class, 'postChangePassword']);
    Route::get('/my-page', [SellerController::class, 'myPage'])->name('my-page');
    Route::get('/profile', [SellerController::class, 'profile'])->name('profile');
    Route::put('/profile', [SellerController::class, 'updateProfile']);

    Route::name('educational_institution.')->prefix('educational_institutions')->group(function () {
        Route::get('get-all', [EducationalInstitutionController::class, 'getAll']);
        Route::get('/{id}/detail', [EducationalInstitutionController::class, 'show']);
        Route::get('/', [EducationalInstitutionController::class, 'index'])->name('list');
        Route::get('/get-list', [EducationalInstitutionController::class, 'getList']);
        Route::get('/create', [EducationalInstitutionController::class, 'create']);
        Route::post('create', [EducationalInstitutionController::class, 'store']);
        Route::get('/{id}/edit', [EducationalInstitutionController::class, 'edit']);
        Route::put('/{id}', [EducationalInstitutionController::class, 'update']);
        Route::get('check-teacher-email', [EducationalInstitutionController::class, 'checkIfStaffEmailNotExists']);
        Route::get('get-by-agency', [SellerController::class, 'getSellersByAgency']);
    });
    Route::name('seller.')->prefix('sellers')->group(function () {
        Route::get('/', [SellerController::class, 'index'])->name('list');
        Route::get('create', [SellerController::class, 'create'])->name('create');
        Route::post('/', [SellerController::class, 'store']);
        Route::get('{seller_id}/edit', [SellerController::class, 'edit']);
        Route::get('{seller_id}/detail', [SellerController::class, 'detail']);
        Route::put('/{seller_id}', [SellerController::class, 'update']);
        Route::delete('/{seller_id}', [SellerController::class, 'delete']);
        Route::get('check-email', [SellerController::class, 'checkEmailExist']);
    });
    Route::name('agency.')->prefix('agencies')->group(function () {
        Route::get('/{agency_id}/educational-institutions', [EducationalInstitutionController::class, 'getEducationalInstitutionsByAgency']);
    });

    Route::name('educational_staff.')->prefix('educational_staffs')->group(function () {
        Route::get('/', [EducationalStaffController::class, 'index']);
        Route::get('/list_edu_institution', [EducationalStaffController::class, 'getListEducationInstitution']);
        Route::get('/{educational_staff_id}', [EducationalStaffController::class, 'show']);
        Route::get('/educational_institution/{educational_institution_id}/list_classroom', [EducationalStaffController::class, 'getListClassroom']);
        Route::put('/{educational_staff_id}/change-owner', [EducationalStaffController::class, 'changeOwner']);
    });


});


