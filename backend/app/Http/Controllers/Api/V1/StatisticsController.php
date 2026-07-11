<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __invoke(Request $request, StatisticsService $statistics): JsonResponse
    {
        return response()->json($statistics->forUser($request->user()));
    }
}
