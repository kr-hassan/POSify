<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductReturn;
use App\Models\ReturnItem;
use App\Models\Product;
use App\Models\Warranty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * ReturnService
 * 
 * Handles product return workflow (refund/exchange).
 * 
 * Business Rules:
 * - Return affects inventory stock (increase for returned items)
 * - Return affects sale totals (refund amount)
 * - Partial returns are allowed
 * - Cannot return more than sold quantity
 * - Cannot return already returned items
 * - Returned items cannot be repaired
 * - Warranty becomes VOID after refund
 * - Exchange creates a NEW warranty
 */
class ReturnService
{
    /**
     * Create a return (refund or exchange)
     * 
     * @param Sale $sale
     * @param array $data
     * @return ProductReturn
     * @throws \Exception
     */
    public function createReturn(Sale $sale, array $data): ProductReturn
    {
        $returnType = $data['return_type'] ?? 'refund';
        $items = $data['items'] ?? [];

        if (empty($items)) {
            throw new \Exception('At least one item must be returned.');
        }

        DB::beginTransaction();

        try {
            // Generate return number
            $returnNo = 'RET-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Validate and calculate totals
            $totalRefund = 0;
            $returnItems = [];

            foreach ($items as $itemData) {
                $saleItem = SaleItem::findOrFail($itemData['sale_item_id']);
                
                // Validate quantity
                $returnQuantity = (int) $itemData['quantity'];
                $availableForReturn = $saleItem->available_for_return;

                if ($returnQuantity <= 0) {
                    throw new \Exception("Return quantity must be greater than 0 for item: {$saleItem->product->name}");
                }

                if ($returnQuantity > $availableForReturn) {
                    throw new \Exception("Cannot return more than available quantity for item: {$saleItem->product->name}. Available: {$availableForReturn}");
                }

                // Calculate refund amount (proportional to original price)
                $refundAmount = ($saleItem->price * $returnQuantity);
                $totalRefund += $refundAmount;

                $returnItems[] = [
                    'sale_item' => $saleItem,
                    'quantity' => $returnQuantity,
                    'refund_amount' => $refundAmount,
                    'reason' => $itemData['reason'] ?? null,
                ];
            }

            // Create return record
            $return = ProductReturn::create([
                'return_no' => $returnNo,
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'return_type' => $returnType,
                'total_refund' => $totalRefund,
                'status' => 'pending', // Requires approval
                'created_by' => auth()->id(),
                'approved_by' => null,
                'reason' => $data['reason'] ?? null,
                'return_date' => $data['return_date'] ?? Carbon::today(),
                'processed_date' => null,
            ]);

            // Create return items
            foreach ($returnItems as $itemData) {
                ReturnItem::create([
                    'return_id' => $return->id,
                    'sale_item_id' => $itemData['sale_item']->id,
                    'product_id' => $itemData['sale_item']->product_id,
                    'quantity' => $itemData['quantity'],
                    'refund_amount' => $itemData['refund_amount'],
                    'reason' => $itemData['reason'],
                ]);
            }

            DB::commit();

            return $return->fresh(['returnItems', 'sale', 'customer']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve a return (Admin/Manager only)
     * 
     * @param ProductReturn $return
     * @return ProductReturn
     * @throws \Exception
     */
    public function approveReturn(ProductReturn $return): ProductReturn
    {
        if ($return->status !== 'pending') {
            throw new \Exception('Only pending returns can be approved.');
        }

        DB::beginTransaction();

        try {
            $return->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            DB::commit();

            return $return->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process a return (execute refund/exchange)
     * 
     * This is where inventory and financial adjustments happen.
     * 
     * @param ProductReturn $return
     * @return ProductReturn
     * @throws \Exception
     */
    public function processReturn(ProductReturn $return): ProductReturn
    {
        if ($return->status !== 'approved') {
            throw new \Exception('Only approved returns can be processed.');
        }

        DB::beginTransaction();

        try {
            $return->load('returnItems.saleItem.product', 'sale');

            foreach ($return->returnItems as $returnItem) {
                $saleItem = $returnItem->saleItem;
                $product = $returnItem->product;

                // 1. Update returned quantity on sale item
                $saleItem->increment('returned_quantity', $returnItem->quantity);

                // 2. Increase inventory stock (returned items go back to stock)
                $product->increment('stock', $returnItem->quantity);

                // 3. Handle warranty
                if ($return->return_type === 'refund') {
                    // Refund: Void the warranty
                    $warranty = $saleItem->warranty;
                    if ($warranty && $warranty->status !== 'void') {
                        $warranty->update(['status' => 'void']);
                    }
                } elseif ($return->return_type === 'exchange') {
                    // Exchange: Create new warranty for new product (if exchange product has warranty)
                    // Note: This assumes the exchange product is specified in the return data
                    // For now, we'll void the old warranty
                    $warranty = $saleItem->warranty;
                    if ($warranty && $warranty->status !== 'void') {
                        $warranty->update(['status' => 'void']);
                    }
                    
                    // TODO: If exchange product is specified, create new warranty
                    // This would require additional data in the return request
                }
            }

            // 4. Update customer balance (reduce due amount or add credit)
            if ($return->sale->customer_id) {
                $customer = $return->sale->customer;
                // If customer had a due, reduce it; otherwise, this is a credit
                if ($return->sale->due_amount > 0) {
                    // Reduce due amount
                    $customer->decrement('balance', min($return->total_refund, $return->sale->due_amount));
                } else {
                    // This is a refund, customer gets credit (negative balance)
                    $customer->decrement('balance', $return->total_refund);
                }
            }

            // 5. Mark return as processed
            $return->update([
                'status' => 'processed',
                'processed_date' => Carbon::today(),
            ]);

            DB::commit();

            return $return->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject a return
     * 
     * @param ProductReturn $return
     * @param string|null $reason
     * @return ProductReturn
     * @throws \Exception
     */
    public function rejectReturn(ProductReturn $return, ?string $reason = null): ProductReturn
    {
        if ($return->status !== 'pending') {
            throw new \Exception('Only pending returns can be rejected.');
        }

        DB::beginTransaction();

        try {
            $return->update([
                'status' => 'rejected',
                'reason' => $reason ? ($return->reason . ' [REJECTED: ' . $reason . ']') : $return->reason,
            ]);

            DB::commit();

            return $return->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get returnable items for a sale
     * 
     * @param Sale $sale
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReturnableItems(Sale $sale)
    {
        return $sale->saleItems()
            ->with('product', 'warranty')
            ->get()
            ->filter(function ($saleItem) {
                return $saleItem->can_be_returned;
            });
    }
}

