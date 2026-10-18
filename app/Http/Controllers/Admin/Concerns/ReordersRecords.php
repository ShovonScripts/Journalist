<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

/**
 * Shared drag-to-reorder handling (ADMIN_PANEL.md §3): every registry and every
 * profile sub-resource exposes the same endpoint, so the front end has one
 * contract. Positions are written 1-based; the drag module posts the full,
 * ordered id list.
 */
trait ReordersRecords
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<int>  $ids
     */
    protected function applyOrder(string $modelClass, array $ids): void
    {
        foreach (array_values($ids) as $position => $id) {
            $modelClass::query()->whereKey($id)->update(['sort_order' => $position + 1]);
        }
    }

    protected function reordered(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
