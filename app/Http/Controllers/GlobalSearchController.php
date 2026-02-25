<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * @return JsonResponse<array{results: array<int, array{type: string, id: int, title: string, subtitle: string|null, url: string}>}>
     */
    public function __invoke(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        $q = trim((string) $q);

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        $items = Item::query()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('item_code', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->with('category')
            ->limit(10)
            ->get(['id', 'name', 'item_code', 'brand', 'category_id']);

        foreach ($items as $item) {
            $results[] = [
                'type' => 'item',
                'id' => $item->id,
                'title' => $item->name,
                'subtitle' => $item->item_code ?: ($item->category?->name ?? $item->brand),
                'url' => route('inventory.items.show', $item),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
