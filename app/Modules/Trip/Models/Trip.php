<?php

namespace App\Modules\Trip\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'status',
    ];

    public const STATUSES = [
        'assigned' => 'ASSIGNED',
        'atVendor' => 'AT_VENDOR',
        'picked' => 'PICKED',
        'delivered' => 'DELIVERED',
    ];

    public static function findNonDelivered(int $orderId): ?self
    {
        return Trip::whereOrderId($orderId)
            ->whereNot('status', Trip::STATUSES['delivered'])
            ->latest()
            ->first();
    }
}
