<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Enums\SourceType;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * The authority for every content rule. Conditional fields are convenience;
 * this class is what a hand-crafted POST still has to satisfy
 * (ADMIN_PANEL.md §7).
 */
trait ValidatesContentItem
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content_type' => ['required', Rule::enum(ContentType::class)],
            'source_type' => ['required', Rule::enum(SourceType::class)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'body' => ['nullable', 'string'],
            'publication_id' => ['nullable', 'required_if:source_type,external', 'exists:publications,id'],
            'external_url' => ['nullable', 'required_if:source_type,external', 'url', 'max:2048'],
            'interviewee_name' => ['nullable', 'string', 'max:255'],
            'interviewee_title' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'tags' => ['array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'topics' => ['array'],
            'topics.*' => ['integer', 'exists:topics,id'],
            'related' => ['array'],
            'related.*' => ['integer', 'exists:content_items,id'],
            'featured_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_og_image_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'canonical_url_override' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateSourceType($validator);
            $this->validateSchedule($validator);
        });
    }

    private function validateSourceType(Validator $validator): void
    {
        if ($this->input('source_type') === SourceType::External->value) {
            if (filled($this->input('body'))) {
                $validator->errors()->add(
                    'body',
                    'External work keeps its text at the source — leave the body empty.',
                );
            }

            return;
        }

        if (blank($this->input('body'))) {
            $validator->errors()->add('body', 'Internal articles need a body.');
        }
    }

    private function validateSchedule(Validator $validator): void
    {
        if ($this->input('status') !== ContentStatus::Scheduled->value) {
            return;
        }

        $publishedAt = $this->input('published_at');

        if (blank($publishedAt)) {
            $validator->errors()->add('published_at', 'A scheduled piece needs a publish date.');

            return;
        }

        if (Carbon::parse($publishedAt)->isPast()) {
            $validator->errors()->add('published_at', 'A scheduled date must be in the future.');
        }
    }
}
