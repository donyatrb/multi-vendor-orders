<?php

namespace App\Modules\DelayReport\Requests;

use App\Modules\Auth\Requests\BaseRequest;

class UpdateRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agent_id' => 'required|exists:agents,id',
        ];
    }
}
