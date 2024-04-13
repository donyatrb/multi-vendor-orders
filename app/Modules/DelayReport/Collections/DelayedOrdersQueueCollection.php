<?php

namespace App\Modules\DelayReport\Collections;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DelayedOrdersQueueCollection extends ResourceCollection
{
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'status' => $item->status,
                'agent' => $item->agent?->full_name,
                'order' => [
                    'totalPrice' => $item->order->total_price,
                    'deliveryTime' => $item->order->delivery_time,
                ],
            ];
        });
    }
}
