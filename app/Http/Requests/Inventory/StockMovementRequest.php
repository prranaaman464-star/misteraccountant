<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockMovementRequest extends FormRequest
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
        $rules = [
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->routeIs('inventory.items.stock-out')) {
            $item = $this->route('item');
            $currentStock = $item->inventory?->stock_quantity ?? 0;
            $rules['quantity'][] = 'max:'.(float) $currentStock;
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be greater than zero.',
            'quantity.max' => 'Stock out quantity cannot exceed current stock.',
            'unit.required' => 'Please select a unit.',
        ];
    }
}
