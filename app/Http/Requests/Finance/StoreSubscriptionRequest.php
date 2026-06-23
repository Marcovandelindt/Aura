<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'billing_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_subcategory_id' => ['nullable', 'exists:expense_subcategories,id'],
            'started_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
