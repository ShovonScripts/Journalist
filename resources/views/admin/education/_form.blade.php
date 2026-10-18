<div class="space-y-5">
    <x-admin.field.text name="institution" label="Institution" :value="$entry->institution" required />
    <x-admin.field.text name="program" label="Program / degree" :value="$entry->program" />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-admin.field.text
            name="start_date"
            label="Start date"
            type="date"
            :value="$entry->start_date?->format('Y-m-d')"
        />
        <x-admin.field.text
            name="end_date"
            label="End date"
            type="date"
            :value="$entry->end_date?->format('Y-m-d')"
            hint="Leave blank if ongoing."
        />
    </div>
</div>
