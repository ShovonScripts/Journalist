<div class="space-y-5">
    <x-admin.field.text name="title" label="Award" :value="$entry->title" required />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-admin.field.text name="awarding_body" label="Awarding body" :value="$entry->awarding_body" />
        <x-admin.field.text
            name="year"
            label="Year"
            type="number"
            :value="$entry->year"
            hint="The year the award was given."
        />
    </div>

    <x-admin.field.textarea name="description" label="Description" :value="$entry->description" :rows="3" />
    <x-admin.field.text name="url" label="Link" type="url" :value="$entry->url" hint="Optional — a citation or announcement." />

    <x-admin.media-picker
        name="media_id"
        label="Certificate / photo"
        :value="$entry->media_id"
        :preview="$entry->media?->url()"
    />
</div>
