<?php

use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\EventController;
// use App\Http\Controllers\Api\ProductController;
// use App\Http\Controllers\Api\SaleController;
// use App\Http\Controllers\Api\ExpenseController;
// use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\ScholarshipController;
use App\Http\Controllers\Api\ScholarshipApplicationController;
use App\Http\Controllers\Api\ScholarshipReceiptController;
use App\Http\Controllers\Api\CostCategoryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

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
        
        // Application review
        Route::post('/applications/{application}/review', [ScholarshipApplicationController::class, 'review']);
        
        // Receipt verification
        Route::post('/receipts/{receipt}/verify', [ScholarshipReceiptController::class, 'verify']);
        
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

