<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiteGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255',
                              Rule::unique('site_groups', 'name')->ignore($this->route('site_group'))],
            'description' => ['nullable', 'string', 'max:1000'],
            'color'       => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon'        => ['nullable', 'string', 'max:32'],
        ];
    }
}
