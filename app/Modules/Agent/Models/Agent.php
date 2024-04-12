<?php

namespace App\Modules\Agent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'first_name',
        'last_name',
        'vendor_id',
    ];
}
