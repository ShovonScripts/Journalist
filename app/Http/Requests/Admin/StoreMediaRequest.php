<?php

namespace App\Http\Requests\Admin;

use App\Support\SecureUpload;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreMediaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:'.(SecureUpload::MAX_BYTES / 1024),
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($value instanceof UploadedFile && ! SecureUpload::accepts($value->getRealPath())) {
                        $fail('The file must be a JPEG, PNG, WebP or PDF.');
                    }
                },
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
