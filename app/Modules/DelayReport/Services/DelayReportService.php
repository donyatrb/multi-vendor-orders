<?php

namespace App\Modules\DelayReport\Services;

use App\Modules\DelayReport\DTOs\DelayReportResponseDto;
use App\Modules\DelayReport\DTOs\NewDeliveryTimeResponseDto;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Modules\DelayReport\Models\DelayReport;
use App\Modules\Order\Models\Order;
use App\Modules\Trip\Models\Trip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DelayReportService
{
    public function store(int $orderId): DelayReportResponseDto
    {
        /** @var Order $order */
        $order = Order::find($orderId);

        if ($order->deliveryTimeHasNotReached()) {
            return new DelayReportResponseDto(status: false, message: __('delay-report.delivery_time_has_not_been_yet_touched'));
        }

        DB::beginTransaction();
        try {

            DelayReport::create([
                'order_id' => $order->id,
                'vendor_id' => $order->vendor_id,
                'delay_time' => $order->delivery_time->diffInUTCMinutes(now()),
            ]);

            $trip = Trip::findNonDelivered($orderId);

            if ($trip) {
                $res = $this->getNewDeliveryTime(order: $order);
                DB::commit();

                return $res;
            }

            if (DelayedOrdersQueue::checkDelayOrderQueueOfTheOrder(orderId: $orderId)) {
                DB::commit();
                return new DelayReportResponseDto(status: false, message: __('delay-report.delay_has_already_been_submitted_for_this_order'));
            }

            DelayedOrdersQueue::query()->create([
                'order_id' => $orderId,
            ]);

            DB::commit();

            return new DelayReportResponseDto(status: true, message: __('delay-report.order_delay_has_been_successfully_submitted'));
        } catch (\Exception $exception) {
            DB::rollBack();
            logger()->error($exception->getMessage());

            return new DelayReportResponseDto(status: false, message: __('delay-report.error_during_order_delay_submission'));
        }
    }

    public function get(): ?Collection
    {
        return DelayedOrdersQueue::assignableDelayedOrders();
    }

    public function update(int $agentId, int $delayedOrdersQueueId): bool|int
    {
        return DelayedOrdersQueue::find($delayedOrdersQueueId)->update(['agent_id' => $agentId]);
    }

    private function getNewDeliveryTime(Order $order): DelayReportResponseDto
    {
        $apiRes = Http::get(config('services.delay_report.new_delivery_time'))->json();

        $newDeliveryTimeDto = new NewDeliveryTimeResponseDto(status: $apiRes['status'], deliveryTime: $apiRes['deliveryTime'] ?? null, message: $apiRes['message'] ?? null);

        if ($newDeliveryTimeDto->responseIsSuccessful()) {
            $order->delivery_time = $newDeliveryTimeDto->deliveryTime;
            $order->save();

            return new DelayReportResponseDto(status: true, message: __('delay-report.new_delivery_time_has_been_set'));
        }

        throw new \Exception(__('delay-report.new_delivery_time_api_response_is_not_successful'));
    }

    public function getVendorsWeeklyDelayReports(int $perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return DelayReport::vendorsWeeklyReport($perPage);
    }
}
