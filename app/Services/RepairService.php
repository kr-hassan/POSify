<?php

namespace App\Services;

use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * RepairService
 * 
 * Handles warranty-based repair workflow.
 * 
 * Business Rules:
 * - Repair does NOT affect inventory stock
 * - Repair does NOT affect sale totals
 * - Same product is returned to customer
 * - Warranty does NOT reset after repair
 * - Warranty remains valid until original end date
 * 
 * Repair Flow:
 * 1. Create warranty claim with type = repair
 * 2. Track claim lifecycle: Pending → In Repair → Completed → Returned
 */
class RepairService
{
    /**
     * Create a repair claim for a warranty
     * 
     * @param Warranty $warranty
     * @param array $data
     * @return WarrantyClaim
     * @throws \Exception
     */
    public function createRepairClaim(Warranty $warranty, array $data): WarrantyClaim
    {
        // Verify warranty is active
        if (!$warranty->is_active) {
            throw new \Exception('Cannot create repair claim for expired or void warranty.');
        }

        DB::beginTransaction();

        try {
            // Generate claim number
            $claimNo = 'RPR-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $claim = WarrantyClaim::create([
                'claim_no' => $claimNo,
                'warranty_id' => $warranty->id,
                'user_id' => auth()->id(),
                'claim_type' => 'repair',
                'issue_description' => $data['issue_description'],
                'status' => 'pending',
                'claim_date' => $data['claim_date'] ?? Carbon::today(),
                'received_date' => null, // Will be set when product is received
                'returned_date' => null, // Will be set when product is returned
                'technician_notes' => null,
            ]);

            // Update warranty status to claimed if first claim
            if ($warranty->warrantyClaims()->count() == 1) {
                $warranty->update(['status' => 'claimed']);
            }

            DB::commit();

            return $claim;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark repair claim as received (product received for repair)
     * 
     * @param WarrantyClaim $claim
     * @param array $data
     * @return WarrantyClaim
     * @throws \Exception
     */
    public function markAsReceived(WarrantyClaim $claim, array $data = []): WarrantyClaim
    {
        if ($claim->claim_type !== 'repair') {
            throw new \Exception('This claim is not a repair claim.');
        }

        if ($claim->status !== 'pending') {
            throw new \Exception('Only pending claims can be marked as received.');
        }

        DB::beginTransaction();

        try {
            $claim->update([
                'status' => 'in_progress',
                'received_date' => $data['received_date'] ?? Carbon::today(),
                'technician_notes' => $data['technician_notes'] ?? null,
            ]);

            DB::commit();

            return $claim->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark repair claim as completed (repair finished, ready to return)
     * 
     * @param WarrantyClaim $claim
     * @param array $data
     * @return WarrantyClaim
     * @throws \Exception
     */
    public function markAsCompleted(WarrantyClaim $claim, array $data = []): WarrantyClaim
    {
        if ($claim->claim_type !== 'repair') {
            throw new \Exception('This claim is not a repair claim.');
        }

        if ($claim->status !== 'in_progress') {
            throw new \Exception('Only in-progress claims can be marked as completed.');
        }

        DB::beginTransaction();

        try {
            $claim->update([
                'status' => 'completed',
                'resolved_date' => $data['resolved_date'] ?? Carbon::today(),
                'resolution_notes' => $data['resolution_notes'] ?? $claim->resolution_notes,
                'technician_notes' => $data['technician_notes'] ?? $claim->technician_notes,
            ]);

            DB::commit();

            return $claim->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark repair claim as returned (product returned to customer)
     * 
     * @param WarrantyClaim $claim
     * @param array $data
     * @return WarrantyClaim
     * @throws \Exception
     */
    public function markAsReturned(WarrantyClaim $claim, array $data = []): WarrantyClaim
    {
        if ($claim->claim_type !== 'repair') {
            throw new \Exception('This claim is not a repair claim.');
        }

        if ($claim->status !== 'completed') {
            throw new \Exception('Only completed claims can be marked as returned.');
        }

        DB::beginTransaction();

        try {
            $claim->update([
                'returned_date' => $data['returned_date'] ?? Carbon::today(),
            ]);

            // Note: We don't change status to 'returned' as the enum doesn't include it
            // The returned_date field indicates the product has been returned
            // Status remains 'completed' to indicate the repair workflow is done

            DB::commit();

            return $claim->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get repair claims for a sale item
     * 
     * @param SaleItem $saleItem
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRepairClaimsForSaleItem(SaleItem $saleItem)
    {
        $warranty = $saleItem->warranty;
        
        if (!$warranty) {
            return collect([]);
        }

        return $warranty->warrantyClaims()
            ->where('claim_type', 'repair')
            ->orderBy('claim_date', 'desc')
            ->get();
    }
}

