<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $users = User::query()
            ->with(['store', 'roles'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->role($request->string('role')->toString()))
            ->when($request->filled('suspended'), fn ($query) => $request->boolean('suspended')
                ? $query->whereNotNull('suspended_at')
                : $query->whereNull('suspended_at'))
            ->latest('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return AdminUserResource::collection($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['store', 'roles']);

        return response()->json(['user' => new AdminUserResource($user)]);
    }

    public function update(UpdateAdminUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        $user->load(['store', 'roles']);

        return response()->json(['user' => new AdminUserResource($user->refresh())]);
    }

    /**
     * Suspend or restore an account. A suspended user cannot log in.
     */
    public function suspend(Request $request, User $user): JsonResponse
    {
        $request->validate(['suspended' => ['required', 'boolean']]);

        if ($user->is($request->user())) {
            throw new ConflictHttpException('You cannot suspend your own account.');
        }

        $user->update([
            'suspended_at' => $request->boolean('suspended') ? now() : null,
        ]);

        if ($request->boolean('suspended')) {
            $user->tokens()->delete();
        }

        $user->load(['store', 'roles']);

        return response()->json(['user' => new AdminUserResource($user->refresh())]);
    }
}
