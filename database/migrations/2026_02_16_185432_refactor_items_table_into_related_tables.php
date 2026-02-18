<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $items = DB::table('items')->get();

        foreach ($items as $item) {
            DB::table('item_tax_details')->insert([
                'item_id' => $item->id,
                'gst_applicable' => $item->gst_applicable ?? false,
                'hsn_sac_code' => $item->hsn_sac_code,
                'gst_rate' => $item->gst_rate,
                'cgst_rate' => $item->cgst_rate,
                'sgst_rate' => $item->sgst_rate,
                'igst_rate' => $item->igst_rate,
                'cess_rate' => $item->cess_rate,
                'price_inclusive_of_tax' => $item->price_inclusive_of_tax ?? false,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
            DB::table('item_pricing')->insert([
                'item_id' => $item->id,
                'purchase_price' => $item->purchase_price,
                'sale_price' => $item->sale_price,
                'mrp' => $item->mrp,
                'minimum_sale_price' => $item->minimum_sale_price,
                'discount_percent_allowed' => $item->discount_percent_allowed,
                'retail_price' => $item->retail_price,
                'wholesale_price' => $item->wholesale_price,
                'dealer_price' => $item->dealer_price,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
            DB::table('item_inventory')->insert([
                'item_id' => $item->id,
                'primary_unit' => $item->primary_unit,
                'conversion_factor' => $item->conversion_factor,
                'opening_stock_quantity' => $item->opening_stock_quantity,
                'opening_stock_value' => $item->opening_stock_value,
                'stock_quantity' => $item->stock_quantity,
                'reorder_level' => $item->reorder_level,
                'minimum_stock_level' => $item->minimum_stock_level,
                'batch_enabled' => $item->batch_enabled ?? false,
                'expiry_date_tracking' => $item->expiry_date_tracking ?? false,
                'serial_number_tracking' => $item->serial_number_tracking ?? false,
                'godown_warehouse' => $item->godown_warehouse,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
            DB::table('item_compliance')->insert([
                'item_id' => $item->id,
                'e_invoice_applicable' => $item->e_invoice_applicable ?? false,
                'e_way_bill_applicable' => $item->e_way_bill_applicable ?? false,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('item_code')->constrained()->nullOnDelete();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'item_category',
                'gst_applicable',
                'hsn_sac_code',
                'gst_rate',
                'cgst_rate',
                'sgst_rate',
                'igst_rate',
                'cess_rate',
                'purchase_price',
                'sale_price',
                'mrp',
                'minimum_sale_price',
                'discount_percent_allowed',
                'price_inclusive_of_tax',
                'retail_price',
                'wholesale_price',
                'dealer_price',
                'primary_unit',
                'conversion_factor',
                'opening_stock_quantity',
                'opening_stock_value',
                'stock_quantity',
                'reorder_level',
                'minimum_stock_level',
                'batch_enabled',
                'expiry_date_tracking',
                'serial_number_tracking',
                'godown_warehouse',
                'e_invoice_applicable',
                'e_way_bill_applicable',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('purchase_price', 15, 4)->nullable();
            $table->decimal('sale_price', 15, 4)->nullable();
            $table->decimal('mrp', 15, 4)->nullable();
            $table->decimal('minimum_sale_price', 15, 4)->nullable();
            $table->decimal('discount_percent_allowed', 8, 2)->nullable();
            $table->decimal('retail_price', 15, 4)->nullable();
            $table->decimal('wholesale_price', 15, 4)->nullable();
            $table->decimal('dealer_price', 15, 4)->nullable();
            $table->string('primary_unit', 50)->nullable();
            $table->decimal('conversion_factor', 15, 4)->nullable();
            $table->decimal('opening_stock_quantity', 15, 4)->nullable();
            $table->decimal('opening_stock_value', 15, 4)->nullable();
            $table->decimal('stock_quantity', 15, 4)->nullable();
            $table->decimal('reorder_level', 15, 4)->nullable();
            $table->decimal('minimum_stock_level', 15, 4)->nullable();
            $table->boolean('batch_enabled')->default(false);
            $table->boolean('expiry_date_tracking')->default(false);
            $table->boolean('serial_number_tracking')->default(false);
            $table->string('godown_warehouse', 100)->nullable();
            $table->string('item_category', 100)->nullable();
            $table->boolean('gst_applicable')->default(false);
            $table->string('hsn_sac_code', 50)->nullable();
            $table->decimal('gst_rate', 8, 2)->nullable();
            $table->decimal('cgst_rate', 8, 2)->nullable();
            $table->decimal('sgst_rate', 8, 2)->nullable();
            $table->decimal('igst_rate', 8, 2)->nullable();
            $table->decimal('cess_rate', 8, 2)->nullable();
            $table->boolean('price_inclusive_of_tax')->default(false);
            $table->boolean('e_invoice_applicable')->default(false);
            $table->boolean('e_way_bill_applicable')->default(false);
        });

        $items = DB::table('items')->get();
        foreach ($items as $item) {
            $tax = DB::table('item_tax_details')->where('item_id', $item->id)->first();
            $pricing = DB::table('item_pricing')->where('item_id', $item->id)->first();
            $inv = DB::table('item_inventory')->where('item_id', $item->id)->first();
            $comp = DB::table('item_compliance')->where('item_id', $item->id)->first();
            if ($tax || $pricing || $inv || $comp) {
                DB::table('items')->where('id', $item->id)->update([
                    'item_category' => null,
                    'gst_applicable' => $tax?->gst_applicable ?? false,
                    'hsn_sac_code' => $tax?->hsn_sac_code,
                    'gst_rate' => $tax?->gst_rate,
                    'cgst_rate' => $tax?->cgst_rate,
                    'sgst_rate' => $tax?->sgst_rate,
                    'igst_rate' => $tax?->igst_rate,
                    'cess_rate' => $tax?->cess_rate,
                    'price_inclusive_of_tax' => $tax?->price_inclusive_of_tax ?? false,
                    'purchase_price' => $pricing?->purchase_price,
                    'sale_price' => $pricing?->sale_price,
                    'mrp' => $pricing?->mrp,
                    'minimum_sale_price' => $pricing?->minimum_sale_price,
                    'discount_percent_allowed' => $pricing?->discount_percent_allowed,
                    'retail_price' => $pricing?->retail_price,
                    'wholesale_price' => $pricing?->wholesale_price,
                    'dealer_price' => $pricing?->dealer_price,
                    'primary_unit' => $inv?->primary_unit,
                    'conversion_factor' => $inv?->conversion_factor,
                    'opening_stock_quantity' => $inv?->opening_stock_quantity,
                    'opening_stock_value' => $inv?->opening_stock_value,
                    'stock_quantity' => $inv?->stock_quantity,
                    'reorder_level' => $inv?->reorder_level,
                    'minimum_stock_level' => $inv?->minimum_stock_level,
                    'batch_enabled' => $inv?->batch_enabled ?? false,
                    'expiry_date_tracking' => $inv?->expiry_date_tracking ?? false,
                    'serial_number_tracking' => $inv?->serial_number_tracking ?? false,
                    'godown_warehouse' => $inv?->godown_warehouse,
                    'e_invoice_applicable' => $comp?->e_invoice_applicable ?? false,
                    'e_way_bill_applicable' => $comp?->e_way_bill_applicable ?? false,
                ]);
            }
        }

        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
    }
};
