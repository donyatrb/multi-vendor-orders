<?php

namespace App\Modules\DelayReport\DTOs;

class DelayReportResponseDto
{
    public function __construct(readonly public bool $status, readonly public string $message)
    {
    }
}
