<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'item_code' => ['nullable', 'string', 'max:100'],
            'item_category' => ['nullable', 'string', 'max:100'],
            'new_category_name' => ['nullable', 'string', 'max:255'],
            'sub_category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model_no' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'gst_applicable' => ['boolean'],
            'hsn_sac_code' => ['nullable', 'string', 'max:50'],
            'gst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cgst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sgst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'igst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cess_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'mrp' => ['nullable', 'numeric', 'min:0'],
            'minimum_sale_price' => ['nullable', 'numeric', 'min:0'],
            'discount_percent_allowed' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'price_inclusive_of_tax' => ['boolean'],
            'retail_price' => ['nullable', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'dealer_price' => ['nullable', 'numeric', 'min:0'],
            'primary_unit' => ['nullable', 'string', 'max:50'],
            'conversion_factor' => ['nullable', 'numeric', 'min:0'],
            'opening_stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'opening_stock_value' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock_level' => ['nullable', 'numeric', 'min:0'],
            'batch_enabled' => ['boolean'],
            'expiry_date_tracking' => ['boolean'],
            'serial_number_tracking' => ['boolean'],
            'godown_warehouse' => ['nullable', 'string', 'max:100'],
            'item_type' => ['required', 'string', 'in:goods,service,raw_material,finished_goods'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'item_image' => ['nullable', 'file', 'image', 'max:2048'],
            'e_invoice_applicable' => ['boolean'],
            'e_way_bill_applicable' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Item name is required.',
            'name.max' => 'Item name cannot exceed 255 characters.',
            'item_type.required' => 'Item type is required.',
            'item_type.in' => 'Please select a valid item type.',
            'status.required' => 'Status is required.',
            'status.in' => 'Please select a valid status.',
            'item_image.image' => 'The uploaded file must be an image.',
            'item_image.max' => 'The image size cannot exceed 2MB.',
        ];
    }
}
