<div class="space-y-5">
    <x-admin.field.text name="name" label="Name" :value="$tag->name" required />
    <x-admin.field.text name="slug" label="Slug" :value="$tag->slug" hint="Leave blank to derive it from the name." />
</div>
