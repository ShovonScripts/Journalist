<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalistProfileRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'photo_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'short_bio' => ['nullable', 'string', 'max:500'],
            'long_bio' => ['nullable', 'string', 'max:20000'],

            'beats' => ['nullable', 'string', 'max:1000'],

            'publications' => ['nullable', 'array'],
            'publications.*' => ['integer', 'exists:publications,id'],

            'social.website' => ['nullable', 'url', 'max:2048'],
            'social.x' => ['nullable', 'url', 'max:2048'],
            'social.facebook' => ['nullable', 'url', 'max:2048'],
            'social.instagram' => ['nullable', 'url', 'max:2048'],
            'social.linkedin' => ['nullable', 'url', 'max:2048'],
            'social.youtube' => ['nullable', 'url', 'max:2048'],
            'social.email' => ['nullable', 'email', 'max:255'],
        ];
    }
}
