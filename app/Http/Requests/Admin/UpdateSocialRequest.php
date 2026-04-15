<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'platform'   => ['required', 'string', 'max:32'],
            'handle'     => ['required', 'string', 'max:255'],
            'url'           => ['required', 'url', 'max:512'],
            'sort_order'    => ['nullable', 'integer', 'min:0'],
            'geo_mode'      => ['nullable', 'string', 'in:all,include,exclude'],
            'geo_countries' => ['nullable', 'string', 'max:255'],
        ];
    }
}
