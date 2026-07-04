<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function read(Request $request, Alert $alert, AlertService $alerts): JsonResponse
    {
        $alert = $alerts->markRead($request->user(), $alert);

        return response()->json([
            'alert' => [
                'id' => $alert->id,
                'category' => $alert->category,
                'title' => $alert->title,
                'subtitle' => $alert->subtitle,
                'dueText' => $alert->due_text,
                'unread' => $alert->unread,
                'readAt' => $alert->read_at?->toIso8601String(),
            ],
        ]);
    }

    public function readAll(Request $request, AlertService $alerts): JsonResponse
    {
        return response()->json([
            'updated' => $alerts->markAllRead($request->user()),
        ]);
    }
}
