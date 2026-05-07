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
            'country_iso' => ['nullable', 'string', 'size:2'],
            'dial_code'   => ['nullable', 'string', 'max:8'],
            'number'      => ['required', 'string', 'max:32'],
            'is_primary'    => ['nullable'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'geo_mode'        => ['nullable', 'string', 'in:all,include,exclude'],
            'geo_countries'   => ['nullable', 'array'],
            'geo_countries.*' => ['string', 'regex:/^[A-Z]{2}$/'],
        ];
    }
}
