<?php

namespace App\Modules\DelayReport\Controllers;

use App\Modules\DelayReport\Requests\StoreRequest;
use App\Modules\DelayReport\Services\DelayReportService;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class DelayReportController extends Controller
{
    private DelayReportService $service;

    public function __construct()
    {
        $this->service = app(DelayReportService::class);
    }

    public function store(StoreRequest $request)
    {
        $storeRes = $this->service->store($request->order_id);
        $status = $storeRes->status;
        $code = $status ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;

        return response()->json([
            'status' => $status,
            'message' => $storeRes->message,
        ], $code);
    }
}
