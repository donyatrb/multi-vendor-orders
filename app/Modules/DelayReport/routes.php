<?php

use App\Modules\DelayReport\Controllers\DelayReportController;
use Illuminate\Support\Facades\Route;

Route::post('order-delay-report', [DelayReportController::class, 'store']);
