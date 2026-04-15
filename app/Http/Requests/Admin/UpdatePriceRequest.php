<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'label'      => ['required', 'string', 'max:255'],
            'amount'     => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'currency'   => ['required', 'string', 'size:3'],
            'period'     => ['nullable', 'string', 'max:32'],
            'is_visible' => ['nullable'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
