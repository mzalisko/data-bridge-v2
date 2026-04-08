<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id'    => ['required', 'integer', 'exists:site_groups,id'],
            'name'        => ['required', 'string', 'max:255'],
            'url'         => ['required', 'url', 'max:512'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active'   => ['boolean'],
        ];
    }
}
