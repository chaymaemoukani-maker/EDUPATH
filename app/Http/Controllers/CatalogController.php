<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogIndexRequest;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Public catalog of published courses with search and category filter.
     */
    public function index(CatalogIndexRequest $request): View
    {
        $filters = $request->validated();

        $courses = Course::query()
            ->with(['category', 'instructor', 'sections.modules'])
            ->withCount('enrollments')
            ->where('status', 'published')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('catalog.index', [
            'courses' => $courses,
            'categories' => $categories,
            'pageTitle' => 'Catalogue des cours',
            'metaDescription' => 'Parcourez le catalogue EduPath et trouvez le cours en ligne qui correspond à vos objectifs. Inscription gratuite accessible à tous.',
            'canonical' => route('catalog', $filters),
        ]);
    }

    /**
     * Public course detail. Content is hidden until enrollment.
     */
    public function show(Course $course): View
    {
        $this->authorize('view', $course);

        $course->load(['category', 'instructor', 'sections.modules.quiz']);
        $course->loadCount('enrollments');

        $totalModules = $course->sections->sum(fn ($section) => $section->modules->count());
        $enrolled = auth()->check() && $course->enrollments()->where('user_id', auth()->id())->exists();

        return view('catalog.show', [
            'course' => $course,
            'totalModules' => $totalModules,
            'enrolled' => $enrolled,
            'pageTitle' => $course->title,
            'metaDescription' => Str::limit(strip_tags($course->description ?? ''), 160),
            'canonical' => route('catalog.show', $course),
        ]);
    }
}