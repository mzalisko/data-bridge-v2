<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhoneRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'label'       => ['nullable', 'string', 'max:100'],
            'country_iso' => ['required', 'string', 'size:2'],
            'dial_code'   => ['required', 'string', 'max:8'],
            'number'      => ['required', 'string', 'max:32'],
            'is_primary'  => ['nullable'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }
}
