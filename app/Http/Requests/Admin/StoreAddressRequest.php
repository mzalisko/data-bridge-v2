<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'label'       => ['nullable', 'string', 'max:100'],
            'country_iso' => ['required', 'string', 'size:2'],
            'city'        => ['required', 'string', 'max:255'],
            'street'      => ['nullable', 'string', 'max:255'],
            'building'    => ['nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'is_primary'    => ['nullable'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'geo_mode'      => ['nullable', 'string', 'in:all,include,exclude'],
            'geo_countries' => ['nullable', 'string', 'max:255'],
        ];
    }
}
