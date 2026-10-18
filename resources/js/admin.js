import Alpine from 'alpinejs';

Alpine.data('mediaPicker', (config) => ({
    endpoint: config.endpoint,
    uploadUrl: config.upload,
    csrf: config.csrf,
    selected: config.initial ?? null,
    previewUrl: config.preview ?? null,
    open: false,
    term: '',
    items: [],

    async load() {
        const response = await fetch(`${this.endpoint}?q=${encodeURIComponent(this.term)}`, {
            headers: { Accept: 'application/json' },
        });

        this.items = response.ok ? await response.json() : [];
    },

    choose(item) {
        this.selected = item.id;
        this.previewUrl = item.url;
        this.open = false;
    },

    clear() {
        this.selected = null;
        this.previewUrl = null;
    },

    async uploadFile() {
        const input = this.$refs.uploadInput;

        if (! input.files.length) {
            return;
        }

        const body = new FormData();
        body.append('file', input.files[0]);

        const response = await fetch(this.uploadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
            body,
        });

        if (! response.ok) {
            this.uploadError = true;

            return;
        }

        this.choose(await response.json());
        input.value = '';
    },
}));

Alpine.data('contentForm', (state) => ({
    sourceType: state.sourceType,
    contentType: state.contentType,
    dirty: false,

    init() {
        window.addEventListener('beforeunload', (event) => {
            if (! this.dirty) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });
    },

    saved() {
        this.dirty = false;
    },
}));

window.Alpine = Alpine;

Alpine.start();
