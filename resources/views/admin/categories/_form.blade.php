<div class="space-y-5">
    <x-admin.field.text name="name" label="Name" :value="$category->name" required />
    <x-admin.field.text name="slug" label="Slug" :value="$category->slug" hint="Leave blank to derive it from the name." />
    <x-admin.field.textarea name="description" label="Description" :value="$category->description" :rows="3" />
    <x-admin.field.text name="sort_order" label="Sort order" type="number" :value="$category->sort_order ?? 0" hint="Lower numbers appear first." />
</div>
