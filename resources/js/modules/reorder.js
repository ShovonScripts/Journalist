/**
 * Drag-to-reorder lists (ADMIN_PANEL.md §3, §5). A plain ES module, not Alpine
 * noise, because DOM reordering is what Alpine is bad at. On drop the full,
 * ordered id list is posted to the list's `data-reorder` endpoint, which writes
 * `sort_order`. If the write fails the page reloads to resync from the server —
 * there is no client-side state to keep in sync.
 */
class ReorderList {
    constructor(list) {
        this.list = list;
        this.endpoint = list.dataset.reorder;
        this.csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        this.dragging = null;

        list.addEventListener('dragstart', (event) => this.onDragStart(event));
        list.addEventListener('dragover', (event) => this.onDragOver(event));
        list.addEventListener('drop', (event) => event.preventDefault());
        list.addEventListener('dragend', () => this.onDragEnd());
    }

    rows() {
        return [...this.list.querySelectorAll('[data-reorder-item]')];
    }

    onDragStart(event) {
        const handle = event.target.closest('[data-reorder-handle]');

        if (! handle) {
            event.preventDefault();

            return;
        }

        this.dragging = handle.closest('[data-reorder-item]');
        this.dragging.classList.add('opacity-40');

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    }

    onDragOver(event) {
        const target = event.target.closest('[data-reorder-item]');

        if (! target || target === this.dragging || ! this.dragging) {
            return;
        }

        event.preventDefault();

        const rect = target.getBoundingClientRect();
        const after = event.clientY - rect.top > rect.height / 2;

        this.list.insertBefore(this.dragging, after ? target.nextSibling : target);
    }

    async onDragEnd() {
        this.dragging?.classList.remove('opacity-40');
        this.dragging = null;

        await this.persist();
    }

    async persist() {
        const ids = this.rows().map((row) => Number(row.dataset.id));

        try {
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({ ids }),
            });

            if (! response.ok) {
                window.location.reload();
            }
        } catch {
            window.location.reload();
        }
    }
}

export function initReorder() {
    document.querySelectorAll('[data-reorder]').forEach((list) => {
        if (list.dataset.reorderReady === 'true') {
            return;
        }

        list.dataset.reorderReady = 'true';
        new ReorderList(list);
    });
}
