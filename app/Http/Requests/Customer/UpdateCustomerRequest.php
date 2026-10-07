<?php

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->belongsToTeam($this->team()) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'name')
                    ->where('team_id', $this->team()->id)
                    ->ignore($this->customer()),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get the team associated with this request.
     */
    protected function team(): Team
    {
        return $this->route('current_team');
    }

    /**
     * Get the customer being updated.
     */
    protected function customer(): Customer
    {
        return $this->route('customer');
    }
}
