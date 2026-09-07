<x-app-layout
    :page-title="$pageTitle ?? null"
    :meta-description="$metaDescription ?? null"
    :canonical="$canonical ?? null"
>
    @if ($course->image)
        <img src="{{ $course->image }}" alt="{{ $course->title }}" class="mb-6 aspect-video w-full rounded-xl object-cover" />
    @endif

    <h1 class="text-2xl font-semibold text-slate-900">{{ $course->title }}</h1>

    <div class="mt-2 flex flex-wrap items-center gap-3">
        @if ($course->category)
            <x-badge>{{ $course->category->name }}</x-badge>
        @endif
    </div>

    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-slate-500">
        @if ($course->instructor)
            <span class="inline-flex items-center gap-1.5">
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $course->instructor->name }}
            </span>
        @endif
        <span class="inline-flex items-center gap-1.5">
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            {{ $totalModules }} module{{ $totalModules > 1 ? 's' : '' }}
        </span>
        <span class="inline-flex items-center gap-1.5">
            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            {{ $course->enrollments_count }} inscription{{ $course->enrollments_count > 1 ? 's' : '' }}
        </span>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @if ($course->description)
                <div class="prose prose-slate max-w-none text-sm leading-relaxed text-slate-600">
                    <p>{!! nl2br(e($course->description)) !!}</p>
                </div>
            @endif

            <h2 class="mt-8 text-lg font-semibold text-slate-900">Programme du cours</h2>

            @php
                $typeLabels = ['text' => 'Texte', 'video' => 'Vidéo', 'pdf' => 'PDF'];
            @endphp

            <div class="mt-4 space-y-3" x-data="{ openSection: 1 }">
                @foreach ($course->sections->sortBy('order')->values() as $section)
                    @php $sectionIndex = $loop->index + 1; @endphp
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between px-5 py-4 text-left text-sm font-medium text-slate-900 hover:bg-slate-50"
                            @click="openSection = openSection === {{ $sectionIndex }} ? null : {{ $sectionIndex }}"
                        >
                            <span class="flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">{{ $sectionIndex }}</span>
                                {{ $section->title }}
                            </span>
                            <svg
                                class="h-5 w-5 text-slate-400 transition-transform duration-200"
                                :class="openSection === {{ $sectionIndex }} ? 'rotate-180' : ''"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="openSection === {{ $sectionIndex }}" x-cloak class="border-t border-slate-100">
                            <ul class="divide-y divide-slate-50">
                                @foreach ($section->modules->sortBy('order') as $module)
                                    <li class="flex items-center justify-between px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            @if ($module->type === 'text')
                                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            @elseif ($module->type === 'video')
                                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                                                </svg>
                                            @elseif ($module->type === 'pdf')
                                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                            @endif
                                            <span class="text-sm text-slate-700">{{ $module->title }}</span>
                                            @if ($module->quiz)
                                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">Quiz</span>
                                            @endif
                                        </div>
                                        <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                        </svg>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-24">
                @guest
                    <p class="text-sm text-slate-600">Créez un compte gratuitement pour accéder à ce cours.</p>
                    <div class="mt-4 space-y-3">
                        <x-button href="{{ route('register') }}" variant="primary" fullWidth>S'inscrire gratuitement</x-button>
                        <x-button href="{{ route('login') }}" variant="secondary" fullWidth>Connexion</x-button>
                    </div>
                @elseif ($enrolled)
                    <x-alert type="success" class="mb-4">Vous êtes inscrit à ce cours.</x-alert>
                    <x-button href="{{ route('learner.courses.show', $course) }}" variant="primary" fullWidth>Continuer le cours</x-button>
                @else
                    <p class="text-sm text-slate-600">Inscrivez-vous gratuitement pour accéder au contenu.</p>
                    <form method="POST" action="{{ route('learner.enrollments.store', $course) }}" class="mt-4">
                        @csrf
                        <x-button type="submit" variant="primary" fullWidth>S'inscrire gratuitement</x-button>
                    </form>
                @endguest

                <div class="mt-6 border-t border-slate-100 pt-4">
                    <ul class="space-y-2 text-sm text-slate-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            {{ $totalModules }} module{{ $totalModules > 1 ? 's' : '' }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            {{ $course->enrollments_count }} inscription{{ $course->enrollments_count > 1 ? 's' : '' }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
