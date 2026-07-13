<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserAvatarService;
use App\Services\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileAvatarDisplayController extends Controller
{
    public function __invoke(
        Request $request,
        User $user,
        int $size,
        UserAvatarService $avatars,
        UserProfileService $profiles,
    ): StreamedResponse {
        $asPublic = $request->query('preview') === 'public';
        if (! $profiles->canViewAvatar($user, $request->user(), $asPublic)) {
            abort(404);
        }

        $file = $avatars->file($user, $size);
        if (! $file) {
            abort(404);
        }

        $publiclyCacheable = $profiles->avatarIsPublic($user);

        return Storage::disk($file['disk'])->response($file['path'], null, [
            'Cache-Control' => $publiclyCacheable
                ? 'public, max-age=300, must-revalidate'
                : 'private, no-store',
            'Content-Disposition' => 'inline',
            'Content-Type' => 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
            'Vary' => 'Cookie',
        ]);
    }
}
