<div class="space-y-5">
    <x-admin.field.text name="role" label="Role" :value="$entry->role" required />
    <x-admin.field.text name="organization" label="Organisation" :value="$entry->organization" required />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-admin.field.text
            name="start_date"
            label="Start date"
            type="date"
            :value="$entry->start_date?->format('Y-m-d')"
            hint="Leave blank if unknown."
        />
        <x-admin.field.text
            name="end_date"
            label="End date"
            type="date"
            :value="$entry->end_date?->format('Y-m-d')"
            hint="Leave blank for “present”."
        />
    </div>

    <x-admin.field.textarea name="description" label="Description" :value="$entry->description" :rows="3" />
</div>
