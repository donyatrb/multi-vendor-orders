<?php

namespace App\Modules\DelayReport\Collections;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VendorsWeeklyDelayReportCollection extends ResourceCollection
{
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($item) {
            return [
                'id' => $item->id,
                'vendor' => $item->vendor,
                'delay_time' => $item->sum,
            ];
        });
    }
}
