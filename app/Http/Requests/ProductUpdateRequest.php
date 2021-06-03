<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class ProductUpdateRequest extends DataFieldRequest
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
            'name' => (new TextRule(2, 50))->make(),
            'description' => (new TextRule(2, 100))->make(['nullable']),
            'price' => (new AmountRule(0))->make(),
            'weight' => (new AmountRule(0))->make(['nullable']),
            'menu_id' => (new IdentifierRule(0))->make(),
            'category_id' => (new IdentifierRule(0))->make(),
        ];

        return $this->nestWithData($rules);
    }
}
