<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MediaList;
use App\Models\MediaListItem;
use App\Services\MediaListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MediaListController extends Controller
{
    public function index(Request $request, MediaListService $lists): JsonResponse
    {
        return response()->json(['lists' => $lists->all($request->user())]);
    }

    public function store(Request $request, MediaListService $lists): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:1000']]);
        $list = $lists->create($request->user(), $data);

        return response()->json(['list' => $lists->summary($request->user(), $list)], 201);
    }

    public function show(Request $request, MediaList $list, MediaListService $lists): JsonResponse
    {
        return response()->json(['list' => $lists->summary($request->user(), $list)]);
    }

    public function update(Request $request, MediaList $list, MediaListService $lists): JsonResponse
    {
        $data = $request->validate(['name' => ['sometimes', 'required', 'string', 'max:120'], 'description' => ['sometimes', 'nullable', 'string', 'max:1000']]);
        $list = $lists->update($request->user(), $list, $data);

        return response()->json(['list' => $lists->summary($request->user(), $list)]);
    }

    public function destroy(Request $request, MediaList $list, MediaListService $lists): JsonResponse
    {
        $lists->delete($request->user(), $list);

        return response()->json(null, 204);
    }

    public function addItem(Request $request, MediaList $list, MediaListService $lists): JsonResponse
    {
        $data = $request->validate(['media_type' => ['required', Rule::in(['movie', 'show'])], 'media_id' => ['required', 'integer', 'min:1']]);
        $item = $lists->addItem($request->user(), $list, $data['media_type'], (int) $data['media_id']);

        return response()->json(['item' => ['id' => $item->id], 'list' => $lists->summary($request->user(), $list->refresh())], 201);
    }

    public function removeItem(Request $request, MediaList $list, MediaListItem $item, MediaListService $lists): JsonResponse
    {
        $lists->removeItem($request->user(), $list, $item);

        return response()->json(null, 204);
    }

    public function reorder(Request $request, MediaList $list, MediaListService $lists): JsonResponse
    {
        $data = $request->validate(['item_ids' => ['required', 'array'], 'item_ids.*' => ['integer', 'distinct', 'min:1']]);
        $lists->reorder($request->user(), $list, $data['item_ids']);

        return response()->json(['list' => $lists->summary($request->user(), $list->refresh())]);
    }
}
