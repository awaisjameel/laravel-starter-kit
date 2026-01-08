<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\UserData;
use App\Http\Requests\Users\UserCreateRequest;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->latest()
            ->paginate(
                perPage: $request->input('perPage', 10),
                page: $request->input('page', 1),
            )
            ->withQueryString()
            ->through(fn (User $user): UserData => $user->toData());

        return Inertia::render('users/Index', [
            'users' => $users,
        ]);
    }

    public function store(UserCreateRequest $request): RedirectResponse
    {
        $userData = UserData::from($request->validated());
        User::create($userData->toArray());

        return redirect()->route('users.index')
            ->with('message', 'User created successfully');
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {

        $user->name = $request->validated('name');
        $user->email = $request->validated('email');
        $user->role = $request->validated('role');
        if ($request->validated('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('message', 'User updated successfully');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('message', 'User deleted successfully');
    }
}
