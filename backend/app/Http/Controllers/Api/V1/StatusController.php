<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $databaseReady = true;

        try {
            DB::select('select 1');
        } catch (\Throwable) {
            $databaseReady = false;
        }

        return response()->json([
            'app' => [
                'ready' => true,
                'name' => config('app.name'),
                'environment' => app()->environment(),
            ],
            'database' => [
                'ready' => $databaseReady,
                'connection' => config('database.default'),
            ],
            'queue' => [
                'connection' => config('queue.default'),
            ],
        ], $databaseReady ? 200 : 503);
    }
}
