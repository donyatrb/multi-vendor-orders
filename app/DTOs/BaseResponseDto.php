<?php

namespace App\DTOs;

use Symfony\Component\HttpFoundation\Response;

class BaseResponseDto
{
    const SUCCESS = 'success';

    const FAILED = 'failed';

    public ?string $status;

    public ?int $code;

    public ?string $data;

    public ?array $messages;

    public function __construct(string $status = self::SUCCESS, int $code = Response::HTTP_OK, ?string $data = null, array $messages = [])
    {
        $this->status = $status;
        $this->code = $code;
        $this->data = $data;
        $this->messages = $messages;
    }
}
