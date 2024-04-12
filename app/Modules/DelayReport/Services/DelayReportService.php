<?php

namespace App\Modules\DelayReport\Services;

use App\Modules\DelayReport\DTOs\DelayReportResponseDto;
use App\Modules\DelayReport\DTOs\NewDeliveryTimeResponseDto;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Modules\DelayReport\Models\DelayReport;
use App\Modules\Order\Models\Order;
use App\Modules\Trip\Models\Trip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DelayReportService
{
    public function store(int $orderId): DelayReportResponseDto
    {
        $trip = Trip::findNonDelivered($orderId);

        /** @var Order $order */
        $order = Order::find($orderId);

        if ($order->deliveryTimeHasNotReached()) {
            return new DelayReportResponseDto(status: false, message: 'delivery time has not been yet touched!');
        }

        DB::beginTransaction();
        try {

            DelayReport::create([
                'order_id'   => $order->id,
                'vendor_id'  => $order->vendor_id,
                'delay_time' => $order->delivery_time->diffInUTCMinutes(now()),
            ]);

            if ($trip) {
                $res = $this->getNewDeliveryTime(order: $order);
                DB::commit();
                return $res;
            }

            DelayedOrdersQueue::query()->create([
                'order_id' => $order->id,
            ]);

            DB::commit();

            return new DelayReportResponseDto(status: true, message: 'Order delay has been successfully submitted!');
        } catch(\Exception $exception) {
            DB::rollBack();
            logger()->error($exception->getMessage());
            return new DelayReportResponseDto(status: false, message: 'An error occurred during order delay submission!');
        }
    }

    private function getNewDeliveryTime(Order $order)
    {
        $apiRes = Http::get(config('services.delay_report.new_delivery_time'))->json();

        $newDeliveryTimeDto = new NewDeliveryTimeResponseDto(status: $apiRes['status'], deliveryTime: $apiRes['deliveryTime'], message: $apiRes['message'] ?? null);

        if ($newDeliveryTimeDto->responseIsSuccessful()) {
            $order->delivery_time = $newDeliveryTimeDto->deliveryTime;
            $order->save();

            return new DelayReportResponseDto(status: true, message: 'New delivery time has been set!');
        }

        throw new \Exception('New delivery time api response is not successful!');
    }
}
