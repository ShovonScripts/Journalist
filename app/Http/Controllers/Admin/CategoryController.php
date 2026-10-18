<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReordersRecords;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Support\Slug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use ReordersRecords;

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->withCount('contentItems')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', ['category' => new Category]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $this->prepare($request->validated(), null);

        Category::query()->create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($this->prepare($request->validated(), $category->id));

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->contentItems()->exists()) {
            return back()->withErrors([
                'category' => 'This category is in use. Refile its content first.',
            ]);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    public function reorder(ReorderRequest $request): JsonResponse
    {
        $this->applyOrder(Category::class, $request->validated('ids'));

        return $this->reordered();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepare(array $data, ?int $ignoreId): array
    {
        $data['slug'] = Slug::uniqueFor(Category::class, ($data['slug'] ?? null) ?: $data['name'], $ignoreId);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
