<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Public homepage: value proposition, featured courses, categories and real platform stats.
     */
    public function index(): View
    {
        $featuredCourses = Course::query()
            ->where('status', 'published')
            ->with(['category', 'instructor', 'sections.modules'])
            ->withCount('enrollments')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $categories = Category::query()
            ->withCount(['courses' => fn ($query) => $query->where('status', 'published')])
            ->orderBy('name')
            ->get();

        return view('welcome', [
            'featuredCourses' => $featuredCourses,
            'categories' => $categories,
            'publishedCoursesCount' => Course::where('status', 'published')->count(),
            'learnersCount' => User::whereHas('roles', fn ($query) => $query->where('name', 'learner'))->count(),
            'instructorsCount' => User::whereHas('roles', fn ($query) => $query->where('name', 'instructor'))->count(),
            'certificatesCount' => Certificate::count(),
            'pageTitle' => 'Apprenez à votre rythme, en ligne',
            'metaDescription' => 'EduPath est la plateforme e-learning qui vous accompagne : inscrivez-vous gratuitement, suivez des cours structurés en sections et modules, validez vos connaissances avec des quiz et obtenez un certificat PDF vérifiable.',
            'canonical' => url('/'),
        ]);
    }
}