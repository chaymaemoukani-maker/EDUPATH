<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(): View
    {
        $users = User::with('roles')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Change a user's role via Laratrust.
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        $this->authorize('updateRole', [$user, $request->validated('role')]);

        if ($user->is(auth()->user()) && $request->validated('role') !== 'admin') {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas retirer votre propre rôle administrateur.');
        }

        $user->syncRoles([$request->validated('role')]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Le rôle de {$user->name} a été mis à jour.");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->is(auth()->user())) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}