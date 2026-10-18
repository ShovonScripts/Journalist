<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Models\Media;
use App\Support\MediaUsage;
use App\Support\SecureUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

/**
 * The single media library (ADMIN_PANEL.md §9). Upload-only in this phase;
 * URL import arrives with its SSRF guard later. Every image field in the panel
 * posts here, so there is one upload path and one validation path.
 */
class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString();

        $media = Media::query()
            ->when($filter === 'images', fn ($query) => $query->where('type', MediaType::Image->value))
            ->when($filter === 'missing-alt', fn ($query) => $query->missingAlt())
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'filter' => $filter,
        ]);
    }

    public function pick(Request $request): JsonResponse
    {
        $term = $request->string('q')->toString();

        $results = Media::query()
            ->images()
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($query) use ($term) {
                    $query->where('original_filename', 'like', '%'.$term.'%')
                        ->orWhere('alt_text', 'like', '%'.$term.'%');
                });
            })
            ->latest()
            ->limit(24)
            ->get()
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'url' => $media->url(),
                'alt' => $media->alt_text,
                'filename' => $media->original_filename,
            ]);

        return response()->json($results);
    }

    public function store(StoreMediaRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        try {
            $sanitized = SecureUpload::sanitize($file->getRealPath());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        $path = 'media/'.now()->format('Y/m').'/'.Str::uuid().'.'.$sanitized['extension'];
        Storage::disk('public')->put($path, $sanitized['contents']);

        $media = Media::query()->create([
            'type' => str_starts_with($sanitized['mime'], 'image/') ? MediaType::Image : MediaType::Document,
            'file_path' => $path,
            'disk' => 'public',
            'original_filename' => $file->getClientOriginalName(),
            'alt_text' => $request->input('alt_text'),
            'caption' => $request->input('caption'),
            'uploaded_by' => $request->user()->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $media->id,
                'url' => $media->url(),
                'alt' => $media->alt_text,
                'filename' => $media->original_filename,
            ])->setStatusCode(201);
        }

        return back()->with('status', 'Uploaded “'.$media->original_filename.'”.');
    }

    public function edit(Media $media): View
    {
        return view('admin.media.edit', [
            'media' => $media,
            'references' => MediaUsage::references($media),
        ]);
    }

    public function update(UpdateMediaRequest $request, Media $media): RedirectResponse
    {
        $media->update($request->validated());

        return redirect()
            ->route('admin.media.edit', $media)
            ->with('status', 'Media updated.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $references = MediaUsage::references($media);

        if ($references !== []) {
            return back()->withErrors([
                'media' => 'This file is used by '.implode(', ', $references).'. Unset it there first.',
            ]);
        }

        Storage::disk($media->disk)->delete($media->file_path);
        $media->delete();

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'File deleted.');
    }
}
