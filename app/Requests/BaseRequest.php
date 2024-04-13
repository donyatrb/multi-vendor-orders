<?php

namespace App\Requests;

use App\DTOs\BaseResponseDto;
use App\Exceptions\ApiErrorException;
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

    protected function prepareErrorResponse(array $errors): BaseResponseDto
    {
        $response = new BaseResponseDto();

        $response->code = Response::HTTP_UNPROCESSABLE_ENTITY;
        $response->status = BaseResponseDto::FAILED;
        $response->messages = $errors;

        return $response;
    }
}
