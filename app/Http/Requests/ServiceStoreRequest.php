<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class ServiceStoreRequest extends DataFieldRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => (new TextRule(2, 50))->make(['required']),
            'description' => (new TextRule(2, 100))->make(['nullable']),
            'once_paid_price' => (new AmountRule(0))->make(['nullable', 'required_without:hourly_paid_price']),
            'hourly_paid_price' => (new AmountRule(0))->make(['nullable', 'required_without:once_paid_price']),
            'period_id' => (new IdentifierRule(0))->make(['required']),
            'category_id' => (new IdentifierRule(0))->make(['required']),
        ];

        return $this->nestWithData($rules);
    }
}
