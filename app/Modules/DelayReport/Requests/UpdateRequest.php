<?php

namespace App\Modules\DelayReport\Requests;

use App\Exceptions\ApiErrorException;
use App\Modules\Agent\Models\Agent;
use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use App\Requests\BaseRequest;

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

    protected function prepareForValidation()
    {
        $delayedOrdersQueueId = $this->route('delayedOrdersQueue');
        $delayedOrdersQueue = DelayedOrdersQueue::find($delayedOrdersQueueId);

        if (! $delayedOrdersQueue) {

            $response = $this->prepareErrorResponse(['delayedOrdersQueue' => 'Delayed orders queue id not found!']);

            throw new ApiErrorException('', 0, null, responseDto: $response);
        }

        $this->merge([
            'delayedOrdersQueue' => $delayedOrdersQueue,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->validate();

        $validator->after(function ($validator) {

            $agent = Agent::find($this->agent_id);
            if ($agent->hasAssignedDelayedOrder()) {
                $validator->errors()->add('agent_id', 'This agent has open delayed order');
            }

            if (DelayedOrdersQueue::isNotDelayedOrderAssignable($this->delayedOrdersQueue->id)) {
                $validator->errors()->add('delayedOrdersQueue', 'This delayed order is picked by another agent');
            }
        });
    }
}
