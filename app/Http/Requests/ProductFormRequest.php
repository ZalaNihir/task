<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductFormRequest extends FormRequest
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
        $productId = $this->route('product');

        return [
            'category_id' => ['required','integer','exists:categories,id'],
            'name' => ['required','min:3', 'max:255'],
            'sku'=> ['required','max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'description'=>['nullable','max:300'],
            'base_price' => ['required','decimal:0,2'],
            'is_active' => ['required','boolean'],
        ];
    }
}
