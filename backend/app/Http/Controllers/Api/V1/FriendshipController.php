<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use App\Services\FriendshipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    public function index(Request $request, FriendshipService $friendships): JsonResponse
    {
        return response()->json($friendships->lists($request->user()));
    }

    public function requests(Request $request, FriendshipService $friendships): JsonResponse
    {
        return response()->json($friendships->requests($request->user()));
    }

    public function requestFriend(Request $request, User $user, FriendshipService $friendships): JsonResponse
    {
        $friendship = $friendships->request($request->user(), $user);

        return response()->json(['friendship' => $friendships->response($friendship, $request->user())], 201);
    }

    public function accept(Request $request, Friendship $friendship, FriendshipService $friendships): JsonResponse
    {
        $friendship = $friendships->accept($request->user(), $friendship);

        return response()->json(['friendship' => $friendships->response($friendship, $request->user())]);
    }

    public function decline(Request $request, Friendship $friendship, FriendshipService $friendships): JsonResponse
    {
        $friendship = $friendships->decline($request->user(), $friendship);

        return response()->json(['friendship' => $friendships->response($friendship, $request->user())]);
    }

    public function destroy(Request $request, Friendship $friendship, FriendshipService $friendships): JsonResponse
    {
        $friendships->remove($request->user(), $friendship);

        return response()->json(null, 204);
    }

    public function block(Request $request, User $user, FriendshipService $friendships): JsonResponse
    {
        $friendship = $friendships->block($request->user(), $user);

        return response()->json(['friendship' => $friendships->response($friendship, $request->user())]);
    }
}
