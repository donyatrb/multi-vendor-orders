<?php

namespace App\Modules\DelayReport\Services;

use App\Modules\DelayReport\DTOs\DelayReportResponseDto;
use App\Modules\DelayReport\DTOs\NewDeliveryTimeResponseDto;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Modules\DelayReport\Models\DelayReport;
use App\Modules\Order\Models\Order;
use App\Modules\Trip\Models\Trip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DelayReportService
{
    const REPORT_PAGE = 'report_page_';

    public function store(int $orderId): DelayReportResponseDto
    {
        /** @var Order $order */
        $order = Order::find($orderId);

        // here is being checked if delivery time reached
        if ($order->deliveryTimeHasNotReached()) {
            return new DelayReportResponseDto(status: false, message: __('delay-report.delivery_time_has_not_been_yet_touched'));
        }

        DB::beginTransaction();
        try {

            // for both approaches we need to have delay report
            DelayReport::create([
                'order_id' => $order->id,
                'vendor_id' => $order->vendor_id,
                'delay_time' => $order->delivery_time->diffInUTCMinutes(now()),
            ]);

            $trip = Trip::findNonDelivered($orderId);

            // if the trip is not delivered, it determines the new delivery time
            if ($trip) {
                $res = $this->getNewDeliveryTime(order: $order);
                DB::commit();

                return $res;
            }

            // return error if another delay order has been submitted before
            if (DelayedOrdersQueue::checkDelayOrderQueueOfTheOrder(orderId: $orderId)) {
                DB::commit();
                return new DelayReportResponseDto(status: false, message: __('delay-report.delay_has_already_been_submitted_for_this_order'));
            }

            // if this order doesnt have any trip or the trip is delivered, it should be added to delay orders queue
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

    public function getVendorsWeeklyDelayReports(int $perPage, int $page = 1): LengthAwarePaginator
    {
        if (Cache::has(self::REPORT_PAGE . $page)) {
            return Cache::get(self::REPORT_PAGE . $page);
        }

        Cache::remember(self::REPORT_PAGE . $page, 3, function() use ($perPage) {
            return DelayReport::vendorsWeeklyReport($perPage);
        });

        return DelayReport::vendorsWeeklyReport($perPage);
    }
}
