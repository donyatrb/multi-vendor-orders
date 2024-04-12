<?php

namespace App\Modules\DelayReport\Controllers;

use App\Modules\DelayReport\Models\DelayReport;
use App\Modules\DelayReport\Requests\StoreRequest;
use App\Modules\Order\Models\Order;
use App\Modules\Trip\Models\Trip;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class DelayReportController extends Controller
{
    public function store(StoreRequest $request)
    {
        $trip = Trip::whereOrderId($request->order_id)
            ->whereNot('status', Trip::STATUSES['delivered'])
            ->latest()
            ->first();

        $order = Order::find($request->order_id);

        if ($order->delivery_time->greaterThan(now())) {
            return response(['message' => 'delivery time has not been yet touched!']);
        }

        DelayReport::create([
            'order_id' => $order->id,
            'vendor_id' => $order->vendor_id,
            'delay_time' => $order->delivery_time->diffInUTCMinutes(now())
        ]);

        // @todo
        if ($trip) {
           return Http::get(config('services.delay_report.new_delivery_time'))->json();

           // Response DTO and return response

            // call the api
            // update order->delivery_time
        }


    }
}
