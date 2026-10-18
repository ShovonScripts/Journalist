<div class="space-y-5">
    <x-admin.field.text name="name" label="Name" :value="$topic->name" required />
    <x-admin.field.text name="slug" label="Slug" :value="$topic->slug" hint="Leave blank to derive it from the name." />
    <x-admin.field.textarea name="description" label="Description" :value="$topic->description" :rows="4" />
    <x-admin.media-picker
        name="featured_image_media_id"
        label="Featured image"
        :value="$topic->featured_image_media_id"
        :preview="$topic->featuredImage?->url()"
    />
    <x-admin.field.text name="sort_order" label="Sort order" type="number" :value="$topic->sort_order ?? 0" hint="Lower numbers appear first." />
</div>
