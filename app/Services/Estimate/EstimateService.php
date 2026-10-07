<?php

namespace App\Services\Estimate;

use App\Models\Estimate;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EstimateService
{
    /**
     * Create an estimate and snapshot its item lines.
     *
     * @param  array{customer_id: int, issue_date: string, valid_until?: ?string, discount_amount?: ?string, tax_rate?: ?string, notes?: ?string, items: array<int, array{item_id: int, quantity: string}>}  $attributes
     */
    public function create(Team $team, array $attributes): Estimate
    {
        return DB::transaction(function () use ($team, $attributes): Estimate {
            $estimate = $team->estimates()->create([
                'customer_id' => $attributes['customer_id'],
                'number' => $this->nextNumber($team),
                'status' => 'draft',
                'issue_date' => $attributes['issue_date'],
                'valid_until' => $attributes['valid_until'] ?? null,
                'discount_amount' => $attributes['discount_amount'] ?? 0,
                'tax_rate' => $attributes['tax_rate'] ?? 0,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->replaceLines($estimate, $team, $attributes['items']);

            return $estimate->fresh(['lines']);
        });
    }

    /**
     * Update a draft estimate and replace its item snapshots.
     *
     * @param  array{customer_id: int, issue_date: string, valid_until?: ?string, discount_amount?: ?string, tax_rate?: ?string, notes?: ?string, items: array<int, array{item_id: int, quantity: string}>}  $attributes
     */
    public function update(Estimate $estimate, Team $team, array $attributes): void
    {
        $this->ensureIsDraft($estimate);

        DB::transaction(function () use ($estimate, $team, $attributes): void {
            $estimate->update([
                'customer_id' => $attributes['customer_id'],
                'issue_date' => $attributes['issue_date'],
                'valid_until' => $attributes['valid_until'] ?? null,
                'discount_amount' => $attributes['discount_amount'] ?? 0,
                'tax_rate' => $attributes['tax_rate'] ?? 0,
                'notes' => $attributes['notes'] ?? null,
            ]);

            $estimate->lines()->delete();
            $this->replaceLines($estimate, $team, $attributes['items']);
        });
    }

    /**
     * Move an estimate through its allowed status transitions.
     */
    public function transitionTo(Estimate $estimate, string $status): void
    {
        $transitions = [
            'draft' => ['sent'],
            'sent' => ['accepted', 'declined'],
        ];

        if (! in_array($status, $transitions[$estimate->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => 'This estimate cannot transition to the selected status.',
            ]);
        }

        $estimate->update(['status' => $status]);
    }

    /**
     * Delete a draft estimate.
     */
    public function delete(Estimate $estimate): void
    {
        $this->ensureIsDraft($estimate);

        $estimate->delete();
    }

    /**
     * @param  array<int, array{item_id: int, quantity: string}>  $lines
     */
    protected function replaceLines(Estimate $estimate, Team $team, array $lines): void
    {
        $subtotal = 0.0;

        foreach ($lines as $line) {
            $item = $team->items()->whereKey($line['item_id'])->firstOrFail();
            $lineTotal = round((float) $item->unit_price * (float) $line['quantity'], 2);
            $subtotal += $lineTotal;

            $estimate->lines()->create([
                'item_id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'quantity' => $line['quantity'],
                'unit_price' => $item->unit_price,
                'line_total' => $lineTotal,
            ]);
        }

        $discount = min((float) $estimate->discount_amount, $subtotal);
        $taxableAmount = $subtotal - $discount;
        $taxAmount = round($taxableAmount * ((float) $estimate->tax_rate / 100), 2);

        $estimate->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $taxableAmount + $taxAmount,
        ]);
    }

    protected function ensureIsDraft(Estimate $estimate): void
    {
        if ($estimate->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Only draft estimates can be edited.',
            ]);
        }
    }

    protected function nextNumber(Team $team): string
    {
        $nextId = ((int) $team->estimates()->max('id')) + 1;

        return 'EST-'.str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }
}
