<div class="space-y-5">
    <x-admin.field.text name="name" label="Name" :value="$publication->name" required />
    <x-admin.field.text name="slug" label="Slug" :value="$publication->slug" hint="Leave blank to derive it from the name." />
    <x-admin.field.text name="website_url" label="Website URL" type="url" :value="$publication->website_url" placeholder="https://…" />
    <x-admin.field.textarea name="description" label="Description" :value="$publication->description" :rows="3" />
    <x-admin.media-picker
        name="logo_media_id"
        label="Logo"
        :value="$publication->logo_media_id"
        :preview="$publication->logo?->url()"
        hint="Shown small and in grayscale on the public site."
    />
</div>
