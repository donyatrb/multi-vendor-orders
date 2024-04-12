<?php

namespace App\Modules\DelayReport\DTOs;

class NewDeliveryTimeResponseDto
{
    public function __construct(readonly public bool $status,
        readonly public ?string $deliveryTime = null,
        readonly public ?string $message = null)
    {
    }

    public function responseIsSuccessful(): bool
    {
        return $this->status;
    }
}
