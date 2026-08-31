<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateProfileRequest;
use App\Services\Contracts\IProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private IProfileService $profiles) {}

    public function edit(): View
    {
        return view('dashboard.profile.edit');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $this->profiles->update($request->user(), $data);

        return redirect()
            ->route('dashboard.profile.edit')
            ->with('status', 'Profile updated.');
    }
}
