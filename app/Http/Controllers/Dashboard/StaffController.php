<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\ContentResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreStaffRequest;
use App\Http\Requests\Dashboard\UpdateStaffRequest;
use App\Models\User;
use App\Services\Contracts\IStaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(private IStaffService $staffMembers) {}

    public function index(): View
    {
        return view('dashboard.staff.index', [
            'staff' => $this->staffMembers->all(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.staff.create', [
            'resources' => ContentResource::cases(),
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $staff = $this->staffMembers->create(
            $request->safe()->only(['name', 'email', 'password']),
            $request->validated('resources') ?? [],
        );

        return redirect()->route('dashboard.staff.edit', $staff)->with('status', 'Staff member created.');
    }

    public function edit(User $staff): View
    {
        return view('dashboard.staff.edit', $this->staffMembers->editorData($staff));
    }

    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $this->staffMembers->update(
            $staff,
            $data,
            $request->validated('resources') ?? [],
        );

        return back()->with('status', 'Staff member updated.');
    }

    public function toggleStatus(User $staff): JsonResponse
    {
        $staff = $this->staffMembers->toggleStatus($staff);

        return response()->json([
            'on' => $staff->isActive(),
            'label' => $staff->status->label(),
            'message' => $staff->isActive() ? 'Staff marked active.' : 'Staff marked inactive.',
        ]);
    }
}
