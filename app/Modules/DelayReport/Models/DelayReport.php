<?php

namespace App\Modules\DelayReport\Models;

use App\Modules\Vendor\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class DelayReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'agent_id',
        'vendor_id',
        'delay_time',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public static function vendorsWeeklyReport(int $perPage): ?\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return DelayReport::query()->where('created_at', '>=', today()->subWeek()->toDateTimeString())
            ->select(DB::raw('sum(delay_time) as sum, vendor_id'))
            ->groupBy('vendor_id')
            ->orderByDesc('sum')
            ->with('vendor')
            ->paginate($perPage);
    }
}
