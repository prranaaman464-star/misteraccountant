<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StoreInventoryItemRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryItemsController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $query = Item::query()
            ->with(['category', 'pricing', 'inventory'])
            ->latest();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('item_type') && $request->item_type) {
            $query->where('item_type', $request->item_type);
        }

        $perPage = $request->get('per_page', 10);
        $items = $query->paginate($perPage)->withQueryString();

        return Inertia::render('inventory/Items', [
            'items' => $items,
            'filters' => $request->only(['search', 'status', 'item_type', 'per_page']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('inventory/ItemsCreate', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeCategory(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        $category = Category::query()->create([
            'name' => $request->name,
        ]);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    public function store(StoreInventoryItemRequest $request): \Illuminate\Http\RedirectResponse|\Inertia\Response
    {
        try {
            $validated = $request->validated();

            $itemImagePath = null;
            if ($request->hasFile('item_image')) {
                $itemImagePath = $request->file('item_image')->store('items', 'public');
            }

            $categoryId = null;
            if (! empty($validated['new_category_name'] ?? null)) {
                $category = Category::query()->firstOrCreate(['name' => $validated['new_category_name']]);
                $categoryId = $category->id;
            } elseif (! empty($validated['item_category'] ?? null)) {
                $categoryId = Category::where('id', $validated['item_category'])->exists()
                    ? (int) $validated['item_category']
                    : null;
            }

            $item = Item::query()->create([
                'name' => $validated['name'],
                'item_code' => $validated['item_code'] ?? null,
                'category_id' => $categoryId,
                'sub_category' => $validated['sub_category'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'model_no' => $validated['model_no'] ?? null,
                'description' => $validated['description'] ?? null,
                'item_type' => $validated['item_type'],
                'status' => $validated['status'],
                'item_image' => $itemImagePath,
            ]);

            $item->taxDetail()->create([
                'gst_applicable' => (bool) ($validated['gst_applicable'] ?? false),
                'hsn_sac_code' => $validated['hsn_sac_code'] ?? null,
                'gst_rate' => $validated['gst_rate'] ?? null,
                'cgst_rate' => $validated['cgst_rate'] ?? null,
                'sgst_rate' => $validated['sgst_rate'] ?? null,
                'igst_rate' => $validated['igst_rate'] ?? null,
                'cess_rate' => $validated['cess_rate'] ?? null,
                'price_inclusive_of_tax' => (bool) ($validated['price_inclusive_of_tax'] ?? false),
            ]);

            $item->pricing()->create([
                'purchase_price' => $validated['purchase_price'] ?? null,
                'sale_price' => $validated['sale_price'] ?? null,
                'mrp' => $validated['mrp'] ?? null,
                'minimum_sale_price' => $validated['minimum_sale_price'] ?? null,
                'discount_percent_allowed' => $validated['discount_percent_allowed'] ?? null,
                'retail_price' => $validated['retail_price'] ?? null,
                'wholesale_price' => $validated['wholesale_price'] ?? null,
                'dealer_price' => $validated['dealer_price'] ?? null,
            ]);

            $item->inventory()->create([
                'primary_unit' => $validated['primary_unit'] ?? null,
                'conversion_factor' => $validated['conversion_factor'] ?? null,
                'opening_stock_quantity' => $validated['opening_stock_quantity'] ?? null,
                'opening_stock_value' => $validated['opening_stock_value'] ?? null,
                'stock_quantity' => $validated['stock_quantity'] ?? null,
                'reorder_level' => $validated['reorder_level'] ?? null,
                'minimum_stock_level' => $validated['minimum_stock_level'] ?? null,
                'batch_enabled' => (bool) ($validated['batch_enabled'] ?? false),
                'expiry_date_tracking' => (bool) ($validated['expiry_date_tracking'] ?? false),
                'serial_number_tracking' => (bool) ($validated['serial_number_tracking'] ?? false),
                'godown_warehouse' => $validated['godown_warehouse'] ?? null,
            ]);

            $item->compliance()->create([
                'e_invoice_applicable' => (bool) ($validated['e_invoice_applicable'] ?? false),
                'e_way_bill_applicable' => (bool) ($validated['e_way_bill_applicable'] ?? false),
            ]);

            return redirect()->route('inventory.items');
        } catch (\Exception $e) {
            return back()->withErrors([
                'general' => 'An error occurred while saving the item: '.$e->getMessage(),
            ]);
        }
    }

    public function show(string $item): \Inertia\Response
    {
        return Inertia::render('inventory/ItemsShow', ['item' => $item]);
    }

    public function edit(string $item): \Inertia\Response
    {
        return Inertia::render('inventory/ItemsEdit', ['item' => $item]);
    }

    public function update(Request $request, string $item): \Illuminate\Http\RedirectResponse
    {
        // TODO: Add update logic
        return redirect()->route('inventory.items.show', $item);
    }

    public function destroy(string $item): \Illuminate\Http\RedirectResponse
    {
        // TODO: Add delete logic
        return redirect()->route('inventory.items');
    }
}
