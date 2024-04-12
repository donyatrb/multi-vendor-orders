<?php

namespace App\Modules\DelayReport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelayedOrdersQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'agent_id',
        'status',
    ];

    public const STATUSES = [
        'checked' => 'CHECKED',
        'unchecked' => 'UNCHECKED',
        'checking' => 'CHECKING',
    ];

    public static function checkDelayOrderQueueOfTheOrder(int $orderId): bool
    {
        return DelayedOrdersQueue::whereOrderId($orderId)->exists();
    }
}
