<?php

namespace App\Modules\DelayReport\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders',
        ];
    }
}
