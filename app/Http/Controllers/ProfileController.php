<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update($request->safe()->only(['name', 'email']));
        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function destroyAvatar(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->clearMediaCollection('avatar');

        return back()->with('success', 'Foto eliminada correctamente.');
    }
}
