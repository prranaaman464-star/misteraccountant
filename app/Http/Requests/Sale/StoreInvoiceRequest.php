<?php

namespace App\Http\Requests\Sale;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => ['required', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'status' => ['nullable', 'string', 'in:draft,sent,paid,overdue,unpaid,partially_paid'],
            'currency' => ['required', 'string', 'max:10'],
            'enable_tax' => ['boolean'],
            'billed_by' => ['nullable', 'string', 'exists:users,id'],
            'customer_id' => ['nullable', 'string', 'exists:clients,id'],
            'round_off_total' => ['boolean'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'subtotal_amount' => ['nullable', 'numeric', 'min:0'],
            'cgst_amount' => ['nullable', 'numeric', 'min:0'],
            'sgst_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'additional_notes' => ['nullable', 'string', 'max:5000'],
            'terms_and_conditions' => ['nullable', 'string', 'max:5000'],
            'account_id' => ['nullable', 'string', 'max:50'],
            'selected_signature_id' => ['nullable', 'string', 'max:50'],
            'signature_name' => ['nullable', 'string', 'max:255'],
            'is_recurring' => ['boolean'],
            'recurring_frequency' => ['nullable', 'string', 'in:weekly,monthly,quarterly,yearly'],
            'recurring_interval' => ['nullable', 'string', 'max:20'],
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.product_name' => ['required', 'string', 'max:255'],
            'line_items.*.quantity' => ['required', 'numeric', 'min:0'],
            'line_items.*.unit' => ['required', 'string', 'max:50'],
            'line_items.*.rate' => ['required', 'numeric', 'min:0'],
            'line_items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'line_items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'line_items.*.amount' => ['required', 'numeric', 'min:0'],
            'line_items.*.item_id' => ['nullable', 'string', 'exists:items,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'invoice_number' => 'invoice number',
            'invoice_date' => 'invoice date',
            'customer_id' => 'customer',
            'line_items' => 'line items',
            'line_items.*.product_name' => 'product/service name',
            'line_items.*.quantity' => 'quantity',
            'line_items.*.rate' => 'rate',
            'line_items.*.amount' => 'amount',
        ];
    }
}
