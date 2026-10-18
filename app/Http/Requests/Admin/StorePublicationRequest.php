<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'logo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
