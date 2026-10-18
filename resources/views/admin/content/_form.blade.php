@php
    $contentTypeOptions = collect(\App\Enums\ContentType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()]);
    $sourceTypeOptions = collect(\App\Enums\SourceType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()]);
    $statusOptions = collect(\App\Enums\ContentStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()]);

    $selectedTags = collect(old('tags', $item->tags->pluck('id')->all()))->map(fn ($id) => (int) $id);
    $selectedTopics = collect(old('topics', $item->topics->pluck('id')->all()))->map(fn ($id) => (int) $id);
    $selectedRelated = collect(old('related', $item->related->pluck('id')->all()))->map(fn ($id) => (int) $id);
@endphp

<div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="space-y-6">
        {{-- Identity --}}
        <section class="space-y-5 rounded border border-hairline bg-white p-5">
            <h3 class="text-[12px] font-semibold uppercase tracking-wider text-muted">Identity</h3>

            <x-admin.field.text name="title" label="Title" :value="$item->title" required />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field.select name="content_type" label="Content type" :options="$contentTypeOptions" :value="$item->content_type?->value" x-model="contentType" required />
                <x-admin.field.select name="source_type" label="Source type" :options="$sourceTypeOptions" :value="$item->source_type?->value" x-model="sourceType" required />
            </div>

            <x-admin.field.text name="slug" label="Slug" :value="$item->slug" hint="Leave blank to derive it from the title. Changing a published slug writes a redirect." />
        </section>

        {{-- Summary --}}
        <section class="space-y-5 rounded border border-hairline bg-white p-5">
            <h3 class="text-[12px] font-semibold uppercase tracking-wider text-muted">Summary</h3>
            <x-admin.field.textarea name="summary" label="Dek / summary" :value="$item->summary" :rows="3" hint="Also the source for the meta description." />
        </section>

        {{-- Where it lives --}}
        <section class="space-y-5 rounded border border-hairline bg-white p-5">
            <h3 class="text-[12px] font-semibold uppercase tracking-wider text-muted">Where it lives</h3>

            {{-- External --}}
            <div x-show="sourceType === 'external'" x-cloak class="space-y-5">
                <x-admin.field.select name="publication_id" label="Publication" :options="$publications->toArray()" :value="$item->publication_id" placeholder="Choose an outlet…" x-bind:disabled="sourceType !== 'external'" />
                <x-admin.field.text name="external_url" label="Original URL" type="url" :value="$item->external_url" placeholder="https://…" x-bind:disabled="sourceType !== 'external'" />
            </div>

            {{-- Internal --}}
            <div x-show="sourceType === 'internal'" x-cloak class="space-y-5">
                <x-admin.field.rich-text name="body" label="Body" :value="$item->body" x-bind:disabled="sourceType !== 'internal'" />
                <x-admin.field.text name="reading_time_minutes" label="Reading time (minutes)" type="number" :value="$item->reading_time_minutes" hint="Derived from the body on save; override if you like." x-bind:disabled="sourceType !== 'internal'" />
                <x-admin.media-picker name="featured_image_media_id" label="Featured image" :value="$item->featured_image_media_id" :preview="$item->featuredImage?->url()" />
            </div>
        </section>

        {{-- Type-specific --}}
        <section class="space-y-5 rounded border border-hairline bg-white p-5">
            <h3 class="text-[12px] font-semibold uppercase tracking-wider text-muted">Type details</h3>

            <div x-show="contentType === 'interview'" x-cloak class="grid gap-5 sm:grid-cols-2">
                <x-admin.field.text name="interviewee_name" label="Interviewee" :value="$item->interviewee_name" />
                <x-admin.field.text name="interviewee_title" label="Interviewee title" :value="$item->interviewee_title" />
            </div>

            <div x-show="contentType === 'video'" x-cloak>
                <x-admin.field.text name="video_url" label="Video URL" type="url" :value="$item->video_url" placeholder="https://…" />
            </div>

            <p x-show="contentType !== 'interview' && contentType !== 'video'" class="text-[13px] text-muted">
                No extra fields for this type.
            </p>
        </section>

        {{-- Classification --}}
        <section class="space-y-5 rounded border border-hairline bg-white p-5">
            <h3 class="text-[12px] font-semibold uppercase tracking-wider text-muted">Classification</h3>

            <x-admin.field.select name="category_id" label="Category" :options="$categories->toArray()" :value="$item->category_id" placeholder="No category" />

            <label class="block">
                <span class="mb-1 block text-[13px] font-medium">Tags</span>
                <select name="tags[]" multiple size="6" class="block w-full rounded border border-hairline bg-white px-3 py-2 text-sm outline-none focus:border-accent focus:ring-1 focus:ring-accent">
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->id }}" @selected($selectedTags->contains($tag->id))>{{ $tag->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block text-[13px] font-medium">Topics</span>
                <select name="topics[]" multiple size="6" class="block w-full rounded border border-hairline bg-white px-3 py-2 text-sm outline-none focus:border-accent focus:ring-1 focus:ring-accent">
                    @foreach ($topics as $topic)
                        <option value="{{ $topic->id }}" @selected($selectedTopics->contains($topic->id))>{{ $topic->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-1 block text-[13px] font-medium">Related content</span>
                <select name="related[]" multiple size="5" class="block w-full rounded border border-hairline bg-white px-3 py-2 text-sm outline-none focus:border-accent focus:ring-1 focus:ring-accent">
                    @foreach ($allContent as $candidate)
                        <option value="{{ $candidate->id }}" @selected($selectedRelated->contains($candidate->id))>{{ $candidate->title }}</option>
                    @endforeach
                </select>
            </label>
        </section>
    </div>

    {{-- Sidebar: SEO + publishing --}}
    <aside class="space-y-6">
        <section class="space-y-5 rounded border border-hairline bg-white p-5">
            <h3 class="text-[12px] font-semibold uppercase tracking-wider text-muted">Publishing</h3>

            <x-admin.field.select name="status" label="Status" :options="$statusOptions" :value="$item->status?->value ?? 'draft'" required />
            <x-admin.field.datetime name="published_at" label="Publish date" :value="$item->published_at" hint="Times are entered in Asia/Dhaka." />
            <x-admin.field.checkbox name="is_featured" label="Feature this piece" :checked="$item->is_featured ?? false" />
        </section>

        <section class="space-y-5 rounded border border-hairline bg-white p-5">
            <h3 class="text-[12px] font-semibold uppercase tracking-wider text-muted">SEO</h3>

            <x-admin.field.text name="seo_title" label="Meta title" :value="$item->seo_title" />
            <x-admin.field.textarea name="seo_description" label="Meta description" :value="$item->seo_description" :rows="3" />
            <x-admin.media-picker name="seo_og_image_media_id" label="OG image" :value="$item->seo_og_image_media_id" :preview="$item->seoOgImage?->url()" />
            <x-admin.field.text name="canonical_url_override" label="Canonical override" type="url" :value="$item->canonical_url_override" placeholder="https://…" />
        </section>
    </aside>
</div>
