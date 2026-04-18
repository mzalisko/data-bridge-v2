<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomFieldRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $siteId        = $this->route('site')?->id;
        $customFieldId = $this->route('customField')?->id;

        return [
            'field_key'   => [
                'required', 'string', 'max:64',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('site_custom_fields', 'field_key')
                    ->where('site_id', $siteId)
                    ->ignore($customFieldId),
            ],
            'field_value' => ['nullable', 'string', 'max:65535'],
            'field_type'  => ['required', 'string', Rule::in(['text', 'number', 'url', 'email', 'json'])],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'field_key.regex'  => 'Ключ — малими літерами, цифрами та _ (має починатись з літери).',
            'field_key.unique' => 'Такий ключ уже існує для цього сайту.',
        ];
    }
}
