<?php

namespace App\Http\Requests;

use App\Enum\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required','integer','exists:products,id'],
            'warehouse_id' => ['required','integer','exists:warehouses,id'],
            'movement_type' => ['required',Rule::enum(StockMovementType::class)],
            'quantity' => ['required','integer','min:1'],
            'reference_id' => ['nullable','integer'],
            'reference_type' => ['nullable','string','max:255'],
            'note' => ['nullable','string'],
        ];
    }
}
