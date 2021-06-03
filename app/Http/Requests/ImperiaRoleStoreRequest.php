<?php

namespace App\Http\Requests;

use App\Rules\RuleBuilders\AmountRule;
use App\Rules\RuleBuilders\DateRule;
use App\Rules\RuleBuilders\DateTimeRule;
use App\Rules\RuleBuilders\IdentifierRule;
use App\Rules\RuleBuilders\TextRule;
use App\Models\Banquet;

class ImperiaRoleStoreRequest extends DataFieldRequest
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
            'name' => (new TextRule(2, 50))->make(['required', 'unique:imperia_roles']),
            'description' => (new TextRule(2, 100))->make(['nullable']),
            'can_read' => ['required', 'boolean'],
            'can_insert' => ['required', 'boolean'],
            'can_modify' => ['required', 'boolean'],
            'is_owner' => ['required', 'boolean'],
        ];

        return $this->nestWithData($rules);
    }
}
