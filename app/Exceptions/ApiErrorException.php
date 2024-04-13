<?php

namespace App\Exceptions;

use App\DTOs\BaseResponseDto;
use Exception;
use Throwable;

class ApiErrorException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null, public ?BaseResponseDto $responseDto = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json(['status' => $this->responseDto->status, 'message' => $this->responseDto->messages], $this->responseDto->code);
    }
}
