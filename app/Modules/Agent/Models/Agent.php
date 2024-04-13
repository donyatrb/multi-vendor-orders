<?php

namespace App\Modules\Agent\Models;

use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function delayedOrdersQueues(): HasMany
    {
        return $this->hasMany(DelayedOrdersQueue::class);
    }

    public function hasAssignedDelayedOrder(): bool
    {
        return $this->delayedOrdersQueues
            ->filter(function ($queue) {
                return $queue->status !== DelayedOrdersQueue::STATUSES['checked'];
            })
            ->isNotEmpty();
    }
}
