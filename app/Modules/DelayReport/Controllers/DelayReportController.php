<?php

namespace App\Modules\DelayReport\Controllers;

use App\Modules\DelayReport\Collections\DelayedOrdersQueueCollection;
use App\Modules\DelayReport\Collections\VendorsWeeklyDelayReportCollection;
use App\Modules\DelayReport\Requests\StoreRequest;
use App\Modules\DelayReport\Requests\UpdateRequest;
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

    public function index()
    {
        $delayedOrders = $this->service->get();

        return response()->json([
            'status' => true,
            'data' => new DelayedOrdersQueueCollection($delayedOrders),
        ]);
    }

    public function update(UpdateRequest $request, int $delayedOrdersQueueId)
    {
        $updateRes = $this->service->update($request->agent_id, $delayedOrdersQueueId);

        if ($updateRes) {
            return response()->json('', Response::HTTP_NO_CONTENT);
        }

        return response()->json(['status' => false, 'message' => __('general.sth_went_wrong')], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function vendorsWeeklyReport(int $perPage)
    {
        $report = $this->service->getVendorsWeeklyDelayReports($perPage);

        return response()->json([
            'status' => true,
            'data' => new VendorsWeeklyDelayReportCollection($report),
            'total' => $report->total(),
            'currentPage' => $report->currentPage(),
            'lastPage' => $report->lastPage(),
        ]);
    }
}
