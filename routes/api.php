<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScholarshipController;
use App\Http\Controllers\Api\ApplicationAwardController;
use App\Http\Controllers\Api\ScholarshipApplicationController;
use App\Http\Controllers\Api\AdminScholarshipApplicationController;
use App\Http\Controllers\Api\ScholarshipAwardController;
use App\Http\Controllers\Api\ScholarshipBudgetController;
use App\Http\Controllers\Api\CostCategoryController;
use App\Http\Controllers\Api\DisbursementController;
use App\Http\Controllers\Api\DisbursementReceiptController;
use App\Http\Controllers\Api\AwardScheduleController;
use App\Http\Controllers\Api\DisbursementPaymentController;
use App\Http\Controllers\Api\ReceiptVerificationController;
use App\Http\Controllers\Api\ScholarshipReportController;
use App\Http\Controllers\Api\AwardReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);


    // Budgets
    Route::get('/scholarships/{scholarship}/budgets', [ScholarshipBudgetController::class, 'index']);
    Route::get('/scholarships/{scholarship}/budgets/{budget}', [ScholarshipBudgetController::class, 'show']);
  
    // Scholarships
    Route::get('/scholarships', [ScholarshipController::class, 'index']);
    Route::get('/scholarships/{scholarship}', [ScholarshipController::class, 'show']);
    
    // Scholarship Applications
    Route::get('/applications', [ScholarshipApplicationController::class, 'index']);
    Route::get('/my-applications', [ScholarshipApplicationController::class, 'myApplications']);
    Route::post('/applications', [ScholarshipApplicationController::class, 'store'])->middleware('student');
    Route::post('/applications/{application}/documents', [ScholarshipApplicationController::class, 'uploadDocuments'])->middleware('student');
    Route::get('/applications/{application}', [ScholarshipApplicationController::class, 'show']);
    Route::get('/applications/{application}/logs', [ScholarshipApplicationController::class, 'logs']);
    
    // my awards
    Route::get('/my-awards', [ScholarshipAwardController::class, 'index']);
    Route::get('/my-awards/{award}', [ScholarshipAwardController::class, 'myShow']);

    // disbursement reciepts
    Route::post('/disbursements/{disbursement}/receipts', [DisbursementReceiptController::class, 'store']);

    // disbursements
    Route::get('/awards/{award}/disbursements', [DisbursementController::class, 'index']);
    Route::post('/disbursements', [DisbursementController::class, 'store']);
    Route::put('/disbursements/{disbursement}', [DisbursementController::class, 'update']);
    Route::delete('/disbursements/{disbursement}', [DisbursementController::class, 'destroy']);


    // Cost Categories
    Route::get('/cost-categories', [CostCategoryController::class, 'index']);
    Route::post('/cost-categories', [CostCategoryController::class, 'store'])->middleware('student');
    Route::get('/cost-categories/{costCategory}', [CostCategoryController::class, 'show']);
    Route::put('/cost-categories/{costCategory}', [CostCategoryController::class, 'update'])->middleware('student');
    Route::delete('/cost-categories/{costCategory}', [CostCategoryController::class, 'destroy'])->middleware('student');



    
    // Admin only routes
    Route::middleware('admin')->group(function () {
        // Scholarships management
        Route::post('/scholarships', [ScholarshipController::class, 'store']);
        Route::put('/scholarships/{scholarship}', [ScholarshipController::class, 'update']);
        Route::delete('/scholarships/{scholarship}', [ScholarshipController::class, 'destroy']);

        // Applications management
        Route::get('/admin/applications', [AdminScholarshipApplicationController::class, 'adminIndex']);
        Route::get('/admin/applications/{id}', [AdminScholarshipApplicationController::class, 'adminShow']);
        Route::post('/admin/applications/{id}/review', [ScholarshipApplicationController::class, 'review']);

        Route::post('/scholarships/{scholarship}/budgets', [ScholarshipBudgetController::class, 'store']);
        Route::put('/scholarships/{scholarship}/budgets/{budget}', [ScholarshipBudgetController::class, 'update']);
        Route::delete('/scholarships/{scholarship}/budgets/{budget}', [ScholarshipBudgetController::class, 'destroy']);
        
        Route::get('/cost-categories', [CostCategoryController::class, 'index']);
        Route::post('/cost-categories', [CostCategoryController::class, 'store']);


        ///admin/applications/
        Route::post('/admin/applications/{id}/award', [ApplicationAwardController::class, 'store']);

        Route::post('/admin/applications/{id}/review', [ScholarshipApplicationController::class, 'review']);

        // schedules
        Route::post('/admin/awards/{awardId}/schedules', [AwardScheduleController::class, 'store']);

        // disbursement payment
        Route::post('/admin/disbursements/{id}/pay', [DisbursementPaymentController::class, 'pay']);

        // receipt verification
        Route::post('/admin/receipts/{id}/verify', [ReceiptVerificationController::class, 'verify']);

        // reports
        Route::get('/admin/reports/scholarships/{id}', [ScholarshipReportController::class, 'show']);
        Route::get('/admin/reports/awards/{id}', [AwardReportController::class, 'show']);

    });

});

