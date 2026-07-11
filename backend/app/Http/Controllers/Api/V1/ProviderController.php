<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PlaybackSource;
use App\Services\ProviderCatalogService;
use App\Services\ProviderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class ProviderController extends Controller
{
    public function index(Request $request, ProviderService $providers): JsonResponse
    {
        return response()->json(['providers' => $providers->list($request->user())]);
    }

    public function test(Request $request, ProviderService $providers): JsonResponse
    {
        return response()->json($providers->test($request->user(), $this->validated($request, false)));
    }

    public function store(Request $request, ProviderService $providers): JsonResponse
    {
        $source = $providers->create($request->user(), $this->validated($request, true));

        return response()->json(['provider' => $providers->summary($source)], 201);
    }

    public function update(Request $request, PlaybackSource $provider, ProviderService $providers): JsonResponse
    {
        $source = $providers->update($request->user(), $provider, $this->validated($request, false));

        return response()->json(['provider' => $providers->summary($source)]);
    }

    public function refresh(Request $request, PlaybackSource $provider, ProviderCatalogService $catalog, ProviderService $providers): JsonResponse
    {
        try {
            $summary = $catalog->refresh($request->user(), $provider);
        } catch (RuntimeException $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }

            return response()->json(['message' => 'Provider refresh failed.', 'errorCode' => $exception->getMessage()], 422);
        }

        return response()->json([
            'provider' => $providers->summary($provider->refresh()->loadCount(['items', 'items as active_items_count' => fn ($query) => $query->where('status', 'available')])),
            'summary' => $summary,
        ]);
    }

    public function destroy(Request $request, PlaybackSource $provider, ProviderService $providers): JsonResponse
    {
        $providers->delete($request->user(), $provider);

        return response()->json(null, 204);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        $providerType = (string) $request->input('provider_type', '');
        $rules = [
            'provider_id' => ['nullable', 'integer'],
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:120'],
            'provider_type' => [$creating ? 'required' : 'sometimes', 'string', Rule::in(['xtream', 'm3u', 'xmltv', 'manual'])],
            'base_url' => [Rule::requiredIf($creating && $providerType === 'xtream'), 'nullable', 'url:http,https', 'max:2048'],
            'username' => [Rule::requiredIf($creating && $providerType === 'xtream'), 'nullable', 'string', 'max:255'],
            'password' => [Rule::requiredIf($creating && $providerType === 'xtream'), 'nullable', 'string', 'max:1024'],
            'playlist_url' => [Rule::requiredIf($creating && $providerType === 'm3u'), 'nullable', 'url:http,https', 'max:4096'],
            'xmltv_url' => [Rule::requiredIf($creating && $providerType === 'xmltv'), 'nullable', 'url:http,https', 'max:4096'],
            'epg_time_shift' => ['nullable', 'integer', 'min:-24', 'max:24'],
            'refresh_frequency' => ['nullable', 'string', Rule::in(['manual', '6h', '12h', 'daily'])],
            'enabled' => ['nullable', 'boolean'],
            'legal_confirmed' => [$creating ? 'accepted' : 'nullable'],
        ];

        return $request->validate($rules);
    }
}
