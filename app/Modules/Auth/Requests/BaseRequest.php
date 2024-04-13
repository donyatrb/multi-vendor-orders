<?php

namespace App\Modules\Auth\Requests;

use App\Modules\Auth\DTOs\AuthResponseDto;
use App\Modules\Auth\Exceptions\ApiErrorException;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class BaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->messages();

        $response = $this->prepareErrorResponse($errors);

        throw new ApiErrorException('', 0, null, responseDto: $response);
    }

    private function prepareErrorResponse(array $errors): AuthResponseDto
    {
        $response = new AuthResponseDto();

        $response->code = Response::HTTP_UNPROCESSABLE_ENTITY;
        $response->status = AuthResponseDto::FAILED;
        $response->messages = $errors;

        return $response;
    }
}
