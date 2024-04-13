<?php

use App\Modules\DelayReport\Controllers\DelayReportController;
use Illuminate\Support\Facades\Route;

Route::post('order-delay-report', [DelayReportController::class, 'store']);

// url for getting delayed orders in order to check
Route::get('order-delay-report', [DelayReportController::class, 'index']);
Route::patch('order-delay-report/{delayedOrdersQueue}', [DelayReportController::class, 'update']);
Route::get('order-delay-report/vendors/weekly/{perPage}', [DelayReportController::class, 'vendorsWeeklyReport']);
