<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FriendInvite;
use App\Services\FriendInviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendInviteController extends Controller
{
    public function index(Request $request, FriendInviteService $invites): JsonResponse
    {
        return response()->json($invites->listFor($request->user()));
    }

    public function store(Request $request, FriendInviteService $invites): JsonResponse
    {
        return response()->json(['invite' => $invites->create($request->user())], 201);
    }

    public function show(string $token, FriendInviteService $invites): JsonResponse
    {
        return response()->json(['invite' => $invites->preview($token)]);
    }

    public function accept(Request $request, string $token, FriendInviteService $invites): JsonResponse
    {
        return response()->json($invites->accept($token, $request->user()));
    }

    public function destroy(Request $request, FriendInvite $invite, FriendInviteService $invites): JsonResponse
    {
        $invites->revoke($request->user(), $invite);

        return response()->json(null, 204);
    }
}
