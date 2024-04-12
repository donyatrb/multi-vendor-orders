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
}
