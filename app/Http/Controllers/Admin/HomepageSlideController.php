<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Traits\HandlesImageUploads;

class HomepageSlideController extends Controller
{
    use HandlesImageUploads;
    public function index()
    {
        $slides = HomepageSlide::query()
            ->ordered()
            ->get()
            ->groupBy('section');

        $sections = HomepageSlide::sections();

        return view('admin.homepage-slides.index', compact('slides', 'sections'));
    }

    public function create(Request $request)
    {
        $sections = HomepageSlide::sections();
        $selectedSection = $request->query('section', HomepageSlide::SECTION_HERO);

        return view('admin.homepage-slides.create', compact('sections', 'selectedSection'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('background_image')) {
            $data['background_image'] = $this->uploadAndConvertImage($request->file('background_image'), 'homepage-slides');
        }

        if (blank($data['sort_order'] ?? null)) {
            $data['sort_order'] = $this->nextSortOrder($data['section']);
        }

        HomepageSlide::create($data);
        $this->normalizeSortOrders($data['section']);

        return redirect()
            ->route('admin.homepage-slides.index')
            ->with('success', 'تمت إضافة السلايد بنجاح.');
    }

    public function edit(HomepageSlide $homepageSlide)
    {
        $sections = HomepageSlide::sections();

        return view('admin.homepage-slides.edit', compact('homepageSlide', 'sections'));
    }

    public function update(Request $request, HomepageSlide $homepageSlide)
    {
        $data = $this->validatedData($request);
        $oldSection = $homepageSlide->section;

        if ($request->hasFile('background_image')) {
            if ($homepageSlide->background_image && !str_starts_with($homepageSlide->background_image, 'http')) {
                Storage::disk('public')->delete($homepageSlide->background_image);
            }

            $data['background_image'] = $this->uploadAndConvertImage($request->file('background_image'), 'homepage-slides');
        }

        if (blank($data['sort_order'] ?? null)) {
            $data['sort_order'] = $this->nextSortOrder($data['section'], $homepageSlide->id);
        }

        $homepageSlide->update($data);

        $this->normalizeSortOrders($data['section']);

        if ($oldSection !== $data['section']) {
            $this->normalizeSortOrders($oldSection);
        }

        return redirect()
            ->route('admin.homepage-slides.index')
            ->with('success', 'تم تحديث السلايد بنجاح.');
    }

    public function destroy(HomepageSlide $homepageSlide)
    {
        $section = $homepageSlide->section;

        if ($homepageSlide->background_image && !str_starts_with($homepageSlide->background_image, 'http')) {
            Storage::disk('public')->delete($homepageSlide->background_image);
        }

        $homepageSlide->delete();
        $this->normalizeSortOrders($section);

        return redirect()
            ->route('admin.homepage-slides.index')
            ->with('success', 'تم حذف السلايد بنجاح.');
    }

    public function toggleStatus(HomepageSlide $homepageSlide)
    {
        $homepageSlide->update([
            'is_active' => ! $homepageSlide->is_active,
        ]);

        return redirect()
            ->route('admin.homepage-slides.index')
            ->with('success', 'تم تحديث حالة السلايد بنجاح.');
    }

    public function move(HomepageSlide $homepageSlide, string $direction)
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 404);

        $slides = HomepageSlide::query()
            ->where('section', $homepageSlide->section)
            ->ordered()
            ->get()
            ->values();

        $currentIndex = $slides->search(fn (HomepageSlide $slide) => $slide->is($homepageSlide));

        if ($currentIndex === false) {
            return redirect()->route('admin.homepage-slides.index');
        }

        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (! isset($slides[$swapIndex])) {
            return redirect()->route('admin.homepage-slides.index');
        }

        $swapSlide = $slides[$swapIndex];
        $currentSortOrder = $homepageSlide->sort_order;

        $homepageSlide->updateQuietly(['sort_order' => $swapSlide->sort_order]);
        $swapSlide->updateQuietly(['sort_order' => $currentSortOrder]);

        $this->normalizeSortOrders($homepageSlide->section);

        return redirect()
            ->route('admin.homepage-slides.index')
            ->with('success', 'تم تحديث ترتيب السلايد.');
    }

    protected function validatedData(Request $request): array
    {
        $data = $request->validate([
            'section' => ['required', Rule::in(array_keys(HomepageSlide::sections()))],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:1000'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'background_image' => [Rule::requiredIf(! $request->route('homepageSlide')), 'nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'show_overlay' => ['nullable', 'boolean'],
            'overlay_color' => ['nullable', 'string', 'max:20'],
            'overlay_strength' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['show_overlay'] = $request->has('show_overlay');

        return $data;
    }

    protected function nextSortOrder(string $section, ?int $ignoreId = null): int
    {
        return (int) HomepageSlide::query()
            ->where('section', $section)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->max('sort_order') + 1;
    }

    protected function normalizeSortOrders(string $section): void
    {
        HomepageSlide::query()
            ->where('section', $section)
            ->ordered()
            ->get()
            ->values()
            ->each(function (HomepageSlide $slide, int $index) {
                $targetOrder = $index + 1;

                if ((int) $slide->sort_order !== $targetOrder) {
                    $slide->updateQuietly(['sort_order' => $targetOrder]);
                }
            });
    }
}
