<?php

namespace App\Modules\DelayReport\Models;

use App\Modules\Agent\Models\Agent;
use App\Modules\Order\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function checkDelayOrderQueueOfTheOrder(int $orderId): bool
    {
        return DelayedOrdersQueue::whereOrderId($orderId)->exists();
    }

    public static function assignableDelayedOrders(): ?Collection
    {
        return DelayedOrdersQueue::query()
            ->whereNull('agent_id')
            ->with(['agent', 'order'])
            ->orderBy('created_at')
            ->get();
    }

    public static function isNotDelayedOrderAssignable(int $delayedOrderQueueId): bool
    {
        return DelayedOrdersQueue::where('id', $delayedOrderQueueId)
            ->whereNull('agent_id')
            ->doesntExist();
    }
}
