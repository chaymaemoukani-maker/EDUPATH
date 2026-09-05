<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $publishedCourses = Course::where('status', 'published')->count();
        $draftCourses = Course::where('status', 'draft')->count();
        $totalCategories = Category::count();

        $recentUsers = User::latest()->limit(5)->get();
        $recentCourses = Course::with('instructor', 'category')->latest()->limit(5)->get();
        $categories = Category::withCount('courses')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'publishedCourses',
            'draftCourses',
            'totalCategories',
            'recentUsers',
            'recentCourses',
            'categories'
        ));
    }
}