<?php

namespace App\Modules\Order\Models;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int vendor_id
 * @property int items_count
 * @property int total_price
 * @property \DateTime delivery_time
 */
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'items_count',
        'total_price',
        'delivery_time',
    ];

    protected $casts = [
        'delivery_time' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function deliveryTimeHasNotReached(): bool
    {
        return $this->delivery_time->greaterThan(now());
    }
}
