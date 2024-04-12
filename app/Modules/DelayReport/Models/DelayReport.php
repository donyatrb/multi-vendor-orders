<?php

namespace App\Modules\DelayReport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelayReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'agent_id',
        'vendor_id',
        'delay_time',
    ];
}
