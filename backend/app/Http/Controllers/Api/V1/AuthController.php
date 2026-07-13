<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\UserProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request, AnalyticsService $analytics, UserProfileService $profiles): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are invalid.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->status !== UserStatus::Active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This account is disabled.',
            ]);
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now(), 'last_active_at' => now()])->save();
        $profiles->ensureProfile($user);
        $analytics->record('user.login', $user);

        return response()->json(null, 204);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(null, 204);
    }

    public function session(Request $request, UserProfileService $profiles): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['authenticated' => false, 'user' => null]);
        }

        /** @var User $user */
        $user = $profiles->ensureProfile($request->user());

        return response()->json([
            'authenticated' => true,
            'user' => $this->userPayload($user, $profiles),
        ]);
    }

    public function me(Request $request, UserProfileService $profiles): JsonResponse
    {
        /** @var User $user */
        $user = $profiles->ensureProfile($request->user());

        return response()->json([
            'user' => $this->userPayload($user, $profiles),
        ]);
    }

    /** @return array<string, mixed> */
    private function userPayload(User $user, UserProfileService $profiles): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'status' => $user->status->value,
            'username' => $user->username,
            'displayName' => $user->display_name,
            'profileSlug' => $user->profile_slug,
            'avatar' => $profiles->ownAvatarUrl($user),
        ];
    }
}
