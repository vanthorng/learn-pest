<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Models\Team;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['product', 'service'])],
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'sku')
                    ->where('team_id', $this->team()->id)
                    ->ignore($this->item()),
            ],
            'unit_price' => ['required', 'decimal:0,2', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function team(): Team
    {
        return $this->route('current_team');
    }

    protected function item(): Item
    {
        return $this->route('item');
    }
}
