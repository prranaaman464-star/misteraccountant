<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductWisePlController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if (! $dateFrom && ! $dateTo) {
            $dateTo = now()->format('Y-m-d');
            $dateFrom = now()->subMonths(3)->format('Y-m-d');
        }
        $sort = $request->get('sort', 'name');
        $order = $request->get('order', 'asc');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $validSorts = [
            'name', 'item_code', 'hsn_sac_code', 'buying_price', 'selling_price',
            'total_sold_quantity', 'total_purchase_quantity', 'current_stock', 'gross_profit_margin',
        ];
        $sort = in_array($sort, $validSorts, true) ? $sort : 'name';
        $order = strtolower($order) === 'desc' ? 'desc' : 'asc';

        $query = Item::query()
            ->with(['pricing', 'taxDetail'])
            ->select(['items.id', 'items.name', 'items.item_code']);

        $soldSubQuery = StockMovement::query()
            ->select('item_id', DB::raw('COALESCE(SUM(quantity), 0) as total_sold'))
            ->where('type', 'out');

        if ($dateFrom) {
            $soldSubQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $soldSubQuery->whereDate('created_at', '<=', $dateTo);
        }

        $soldSubQuery->groupBy('item_id');

        $purchasedSubQuery = StockMovement::query()
            ->select('item_id', DB::raw('COALESCE(SUM(quantity), 0) as total_purchased'))
            ->where('type', 'in');

        if ($dateFrom) {
            $purchasedSubQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchasedSubQuery->whereDate('created_at', '<=', $dateTo);
        }

        $purchasedSubQuery->groupBy('item_id');

        $query->leftJoinSub($soldSubQuery, 'sold', 'items.id', '=', 'sold.item_id');
        $query->leftJoinSub($purchasedSubQuery, 'purchased', 'items.id', '=', 'purchased.item_id');
        $query->leftJoin('item_pricing', 'items.id', '=', 'item_pricing.item_id');
        $query->leftJoin('item_inventory', 'items.id', '=', 'item_inventory.item_id');
        $query->leftJoin('item_tax_details', 'items.id', '=', 'item_tax_details.item_id');
        $query->selectRaw('COALESCE(sold.total_sold, 0) as total_sold_quantity');
        $query->selectRaw('COALESCE(purchased.total_purchased, 0) as total_purchase_quantity');
        $query->selectRaw('COALESCE(item_inventory.stock_quantity, 0) as current_stock');
        $query->selectRaw('COALESCE(item_pricing.purchase_price, 0) as buying_price');
        $query->selectRaw('COALESCE(item_pricing.sale_price, 0) as selling_price');
        $query->selectRaw('item_tax_details.hsn_sac_code as hsn_sac_code');

        $sortColumn = match ($sort) {
            'item_code' => 'items.item_code',
            'hsn_sac_code' => 'item_tax_details.hsn_sac_code',
            'buying_price' => 'item_pricing.purchase_price',
            'selling_price' => 'item_pricing.sale_price',
            'total_sold_quantity' => DB::raw('COALESCE(sold.total_sold, 0)'),
            'total_purchase_quantity' => DB::raw('COALESCE(purchased.total_purchased, 0)'),
            'current_stock' => 'item_inventory.stock_quantity',
            'gross_profit_margin' => DB::raw('CASE WHEN COALESCE(item_pricing.sale_price, 0) > 0 THEN ((item_pricing.sale_price - COALESCE(item_pricing.purchase_price, 0)) / item_pricing.sale_price) * 100 ELSE 0 END'),
            default => 'items.name',
        };

        $query->orderBy($sortColumn, $order);

        $itemsPaginated = $query->paginate($perPage)->withQueryString();

        $items = $itemsPaginated->getCollection()->map(function ($row) {
            $buyingPrice = (float) ($row->buying_price ?? 0);
            $sellingPrice = (float) ($row->selling_price ?? 0);
            $grossProfitMargin = $sellingPrice > 0
                ? round((($sellingPrice - $buyingPrice) / $sellingPrice) * 100, 2)
                : null;

            return [
                'id' => $row->id,
                'name' => $row->name,
                'item_code' => $row->item_code ?? '-',
                'hsn_sac_code' => $row->hsn_sac_code ?? '-',
                'buying_price' => $buyingPrice,
                'selling_price' => $sellingPrice,
                'total_sold_quantity' => (float) ($row->total_sold_quantity ?? 0),
                'total_purchase_quantity' => (float) ($row->total_purchase_quantity ?? 0),
                'current_stock' => (float) ($row->current_stock ?? 0),
                'average_selling_price' => $sellingPrice,
                'average_buying_price' => $buyingPrice,
                'average_landed_cost' => $buyingPrice,
                'gross_profit_margin' => $grossProfitMargin,
                'package_item' => 'No',
            ];
        });

        $itemsPaginated->setCollection($items);

        return Inertia::render('inventory/ProductWise', [
            'items' => $itemsPaginated,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort' => $sort,
                'order' => $order,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $soldSubQuery = StockMovement::query()
            ->select('item_id', DB::raw('COALESCE(SUM(quantity), 0) as total_sold'))
            ->where('type', 'out');

        if ($dateFrom) {
            $soldSubQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $soldSubQuery->whereDate('created_at', '<=', $dateTo);
        }

        $soldSubQuery->groupBy('item_id');

        $purchasedSubQuery = StockMovement::query()
            ->select('item_id', DB::raw('COALESCE(SUM(quantity), 0) as total_purchased'))
            ->where('type', 'in');

        if ($dateFrom) {
            $purchasedSubQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchasedSubQuery->whereDate('created_at', '<=', $dateTo);
        }

        $purchasedSubQuery->groupBy('item_id');

        $items = Item::query()
            ->leftJoinSub($soldSubQuery, 'sold', 'items.id', '=', 'sold.item_id')
            ->leftJoinSub($purchasedSubQuery, 'purchased', 'items.id', '=', 'purchased.item_id')
            ->leftJoin('item_pricing', 'items.id', '=', 'item_pricing.item_id')
            ->leftJoin('item_inventory', 'items.id', '=', 'item_inventory.item_id')
            ->leftJoin('item_tax_details', 'items.id', '=', 'item_tax_details.item_id')
            ->orderBy('items.name')
            ->get([
                'items.name',
                'items.item_code',
                'item_tax_details.hsn_sac_code',
                DB::raw('COALESCE(item_pricing.purchase_price, 0) as buying_price'),
                DB::raw('COALESCE(item_pricing.sale_price, 0) as selling_price'),
                DB::raw('COALESCE(sold.total_sold, 0) as total_sold_quantity'),
                DB::raw('COALESCE(purchased.total_purchased, 0) as total_purchase_quantity'),
                DB::raw('COALESCE(item_inventory.stock_quantity, 0) as current_stock'),
            ]);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product-wise-profitability.csv"',
        ];

        return response()->stream(function () use ($items) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Item', 'Package Item', 'SKU', 'HSN/SAC', 'Buying Price', 'Selling Price',
                'Total Sold Quantity', 'Total Purchase Quantity', 'Current Stock',
                'Average Selling Price', 'Average Buying Price', 'Average Landed Cost', 'Gross Profit Margin(%)',
            ]);
            foreach ($items as $row) {
                $sellingPrice = (float) $row->selling_price;
                $buyingPrice = (float) $row->buying_price;
                $grossProfitMargin = $sellingPrice > 0
                    ? round((($sellingPrice - $buyingPrice) / $sellingPrice) * 100, 2)
                    : '';

                fputcsv($out, [
                    $row->name,
                    'No',
                    $row->item_code ?? '',
                    $row->hsn_sac_code ?? '',
                    $row->buying_price,
                    $row->selling_price,
                    $row->total_sold_quantity,
                    $row->total_purchase_quantity,
                    $row->current_stock,
                    $row->selling_price,
                    $row->buying_price,
                    $row->buying_price,
                    $grossProfitMargin,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }
}
