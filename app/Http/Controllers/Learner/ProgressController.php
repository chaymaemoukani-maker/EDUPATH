<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ProgressService;
use Illuminate\Http\RedirectResponse;

class ProgressController extends Controller
{
    /**
     * Mark a module as completed for the enrolled learner.
     */
    public function store(Module $module): RedirectResponse
    {
        $this->authorize('learn', $module);

        $created = app(ProgressService::class)->markModuleCompleted(auth()->user(), $module);

        return redirect()
            ->route('learner.modules.show', $module)
            ->with(
                $created ? 'success' : 'info',
                $created ? 'Module terminé, bien joué !' : 'Ce module était déjà terminé.'
            );
    }
}