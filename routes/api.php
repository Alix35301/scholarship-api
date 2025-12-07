<?php

use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\EventController;
// use App\Http\Controllers\Api\ProductController;
// use App\Http\Controllers\Api\SaleController;
// use App\Http\Controllers\Api\ExpenseController;
// use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\ScholarshipController;
use App\Http\Controllers\Api\ApplicationAwardController;
use App\Http\Controllers\Api\ScholarshipApplicationController;
use App\Http\Controllers\Api\ScholarshipAwardController;
use App\Http\Controllers\Api\ScholarshipReceiptController;
use App\Http\Controllers\Api\ScholarshipBudgetController;
use App\Http\Controllers\Api\CostCategoryController;
use App\Http\Controllers\Api\DisbursementController;
use App\Http\Controllers\Api\DisbursementReceiptController;
use App\Http\Controllers\Api\AwardScheduleController;
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

    // Scholarship Receipts
    Route::get('/receipts', [ScholarshipReceiptController::class, 'index']);
    Route::post('/receipts', [ScholarshipReceiptController::class, 'store'])->middleware('student');
    Route::get('/receipts/{receipt}', [ScholarshipReceiptController::class, 'show']);

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
        Route::get('/admin/applications', [ScholarshipApplicationController::class, 'adminIndex']);
        Route::get('/admin/applications/{id}', [ScholarshipApplicationController::class, 'adminShow']);
        Route::post('/admin/applications/{id}/review', [ScholarshipApplicationController::class, 'review']);

        Route::post('/scholarships/{scholarship}/budgets', [ScholarshipBudgetController::class, 'store']);
        Route::put('/scholarships/{scholarship}/budgets/{budget}', [ScholarshipBudgetController::class, 'update']);
        Route::delete('/scholarships/{scholarship}/budgets/{budget}', [ScholarshipBudgetController::class, 'destroy']);
        
        Route::get('/cost-categories', [CostCategoryController::class, 'index']);
        Route::post('/cost-categories', [CostCategoryController::class, 'store']);


        ///admin/applications/
        Route::post('/admin/applications/{id}/award', [ApplicationAwardController::class, 'store']);

        // Application review
        Route::post('/admin/applications/{id}/review', [ScholarshipApplicationController::class, 'review']);

        // schedules
        Route::post('/admin/awards/{awardId}/schedules', [AwardScheduleController::class, 'store']);

        // POST /api/admin/awards/{awardId}/schedules
        // POST /api/admin/disbursements/{id}/pay
        // GET /api/admin/disbursements
        // POST /api/admin/receipts/{id}/verify
        // GET /api/admin/reports/scholarships/{id}
        // GET /api/admin/reports/awards/{awardId}
                
        // Sellers
        // Route::apiResource('sellers', SellerController::class);
    });

    // Events
    /*
    Route::prefix('v1')->group(function () {
        Route::apiResource('events', EventController::class);
        Route::get('events/{event}/dashboard', [EventController::class, 'dashboard']);
        Route::get('events/{event}/report', [EventController::class, 'report']);

        // Products (scoped to events)
        Route::apiResource('events.products', ProductController::class);

        // Sales (scoped to events)
        Route::apiResource('events.sales', SaleController::class);
        Route::patch('events/{event}/sales/{sale}/status', [SaleController::class, 'updateStatus']);

        // Expenses (scoped to events)
        Route::apiResource('events.expenses', ExpenseController::class);
        Route::post('events/{event}/expenses/import', [ExpenseController::class, 'import']);
    });
    */
});

