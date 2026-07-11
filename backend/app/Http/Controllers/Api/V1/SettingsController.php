<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MediaEventSource;
use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\MediaEvent;
use App\Models\Movie;
use App\Models\Show;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $lastImport = MediaEvent::forUser($user)->where('source', MediaEventSource::Import->value)->max('occurred_at');

        return response()->json([
            'profile' => ['name' => $user->name, 'email' => $user->email, 'role' => $user->role->value],
            'privacy' => ['library' => 'private', 'listsDefault' => 'private', 'providerDataInWeb' => false],
            'metadata' => [
                'provider' => 'TMDB',
                'movies' => ['enriched' => Movie::forUser($user)->whereNotNull('metadata_refreshed_at')->count(), 'total' => Movie::forUser($user)->count()],
                'shows' => ['enriched' => Show::forUser($user)->whereNotNull('metadata_refreshed_at')->count(), 'total' => Show::forUser($user)->count()],
                'episodes' => ['enriched' => Episode::forUser($user)->whereNotNull('metadata_refreshed_at')->count(), 'total' => Episode::forUser($user)->count()],
            ],
            'import' => ['source' => 'TV Time', 'lastImportAt' => $lastImport, 'mode' => 'admin-assisted'],
            'export' => ['json' => '/api/v1/exports/json', 'csvDatasets' => ['movies', 'shows', 'episodes', 'movie-watches', 'episode-watches', 'ratings', 'notes']],
            'account' => ['deletionAvailable' => false, 'exportBeforeDeletionRecommended' => true],
            'version' => (string) config('mediahub.version', '1.0.0'),
        ]);
    }
}
