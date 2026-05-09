<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteCustomFieldRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'field_key'   => ['required', 'string', 'max:128'],
            'field_value' => ['required', 'string'],
            'field_type'  => ['nullable', 'string', 'in:text,number,url,email,json'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }
}
