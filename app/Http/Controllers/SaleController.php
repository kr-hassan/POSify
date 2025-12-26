<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Http\Requests\StoreSaleRequest;
use App\Mail\InvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'customer', 'saleItems.product']);
        
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('sale_date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date')) {
            $query->where('sale_date', '>=', $request->from_date);
        } elseif ($request->has('to_date')) {
            $query->where('sale_date', '<=', $request->to_date);
        }
        
        if ($request->has('invoice_no')) {
            $query->where('invoice_no', 'like', "%{$request->invoice_no}%");
        }
        
        $sales = $query->latest()->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function store(StoreSaleRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // Generate invoice number
            $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            
            // Calculate totals
            $subtotal = 0;
            $tax = 0;
            
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock availability
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock}");
                }
                
                $itemTotal = $item['quantity'] * $item['price'];
                $subtotal += $itemTotal;
                
                // Calculate tax if applicable
                if ($product->tax_percent > 0) {
                    $tax += ($itemTotal * $product->tax_percent) / 100;
                }
            }
            
            // Apply discount
            $discount = 0;
            if ($request->discount) {
                if ($request->discount_type === 'percent') {
                    $discount = ($subtotal * $request->discount) / 100;
                } else {
                    $discount = $request->discount;
                }
            }
            
            $totalAmount = $subtotal + $tax - $discount;
            $dueAmount = $totalAmount - $request->paid_amount;
            
            // If no customer selected (walk-in), payment must be full
            if (!$request->customer_id && $dueAmount > 0) {
                throw new \Exception("Walk-in customers must pay in full. Please select a customer for partial payment or pay the full amount.");
            }
            
            // Ensure paid amount is not less than total for walk-in customers
            if (!$request->customer_id && $request->paid_amount < $totalAmount) {
                throw new \Exception("Walk-in customers must pay the full amount of $" . number_format($totalAmount, 2));
            }
            
            // Create sale
            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'user_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'tax' => $tax,
                'paid_amount' => $request->customer_id ? $request->paid_amount : $totalAmount, // Force full payment for walk-in
                'due_amount' => $request->customer_id ? max(0, $dueAmount) : 0, // No due for walk-in
                'payment_method' => $request->payment_method,
                'sale_date' => now()->toDateString(),
            ]);
            
            // Create sale items, update stock, and create warranties
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemTotal = $item['quantity'] * $item['price'];
                
                $saleItem = $sale->saleItems()->create([
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $itemTotal,
                ]);
                
                // Reduce stock and batch quantity if batch is specified
                if (isset($item['batch_id']) && $item['batch_id']) {
                    $batch = \App\Models\ProductBatch::findOrFail($item['batch_id']);
                    if ($batch->remaining_quantity < $item['quantity']) {
                        throw new \Exception("Insufficient batch quantity for {$product->name}. Available: {$batch->remaining_quantity}");
                    }
                    $batch->decrement('remaining_quantity', $item['quantity']);
                }
                
                // Reduce stock
                $product->decrement('stock', $item['quantity']);
                
                // Automatically create warranty if product has warranty period
                $warrantyMonths = $product->warranty_period_months;
                
                // If warranty_period_months is null/0 but warranty_period_days exists, convert it
                if (($warrantyMonths === null || $warrantyMonths == 0) && isset($product->warranty_period_days) && $product->warranty_period_days > 0) {
                    // Convert days to months (approximate: days / 30)
                    $warrantyMonths = (int) round($product->warranty_period_days / 30);
                    // Update product to have months for future sales
                    $product->update(['warranty_period_months' => $warrantyMonths]);
                }
                
                if ($warrantyMonths > 0) {
                    $startDate = Carbon::parse($sale->sale_date);
                    $endDate = $startDate->copy()->addMonths($warrantyMonths);
                    
                    // Generate warranty number
                    $warrantyNo = 'WAR-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                    
                    // Create warranty for this sale item
                    \App\Models\Warranty::create([
                        'warranty_no' => $warrantyNo,
                        'sale_id' => $sale->id,
                        'sale_item_id' => $saleItem->id,
                        'customer_id' => $sale->customer_id,
                        'product_id' => $product->id,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'warranty_period_months' => $warrantyMonths,
                        'status' => 'active',
                    ]);
                }
            }
            
            // Update customer balance if due amount exists
            if ($dueAmount > 0 && $request->customer_id) {
                $customer = \App\Models\Customer::find($request->customer_id);
                if ($customer) {
                    // Only add to balance if there's actually a due amount
                    $customer->increment('balance', $dueAmount);
                }
            } elseif ($request->customer_id && $dueAmount <= 0) {
                // If fully paid, ensure balance doesn't go negative
                $customer = \App\Models\Customer::find($request->customer_id);
                if ($customer && $customer->balance < 0) {
                    $customer->update(['balance' => 0]);
                }
            }
            
            DB::commit();
            
            // Send invoice email
            $emailSent = false;
            $emailAddress = null;
            $emailError = null;
            
            // Determine if we should send email and to which address
            $shouldSendEmail = false;
            $emailToSend = null;
            
            if ($request->customer_id) {
                // Registered customer - check if they have email
                $customer = Customer::find($request->customer_id);
                if ($customer && $customer->email) {
                    // Registered customer with email - send automatically (always)
                    $shouldSendEmail = true;
                    $emailToSend = $customer->email;
                } elseif ($request->customer_email) {
                    // Registered customer without email but provided email
                    // Check if send_email flag is set (for checkbox)
                    $sendEmailFlag = filter_var($request->get('send_email', false), FILTER_VALIDATE_BOOLEAN);
                    if ($sendEmailFlag) {
                        $shouldSendEmail = true;
                        $emailToSend = $request->customer_email;
                    }
                }
            } elseif ($request->customer_email) {
                // Walk-in customer - send only if email provided and checkbox checked
                $sendEmailFlag = filter_var($request->get('send_email', false), FILTER_VALIDATE_BOOLEAN);
                if ($sendEmailFlag) {
                    $shouldSendEmail = true;
                    $emailToSend = $request->customer_email;
                }
            }
            
            // Send email if conditions are met
            if ($shouldSendEmail && $emailToSend) {
                try {
                    \Log::info('Attempting to send invoice email to: ' . $emailToSend);
                    Mail::to($emailToSend)->send(new InvoiceMail($sale));
                    $emailSent = true;
                    $emailAddress = $emailToSend;
                    \Log::info('Invoice email sent successfully to: ' . $emailToSend);
                } catch (\Exception $e) {
                    // Log error but don't fail the sale
                    $emailError = $e->getMessage();
                    \Log::error('Failed to send invoice email to ' . $emailToSend . ': ' . $emailError);
                    \Log::error('Email error details: ' . $e->getTraceAsString());
                }
            } else {
                \Log::info('Email not sent. shouldSendEmail: ' . ($shouldSendEmail ? 'true' : 'false') . ', emailToSend: ' . ($emailToSend ?? 'null'));
            }
            
            $response = [
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale_id' => $sale->id,
                'invoice_no' => $invoiceNo,
                'invoice_url' => route('sales.invoice', $sale),
            ];
            
            if ($emailSent) {
                $response['email_sent'] = true;
                $response['email_address'] = $emailAddress;
            } elseif ($emailError) {
                $response['email_error'] = $emailError;
            }
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['user', 'customer', 'saleItems.product.category', 'saleItems.warranty', 'saleReturns']);
        return view('sales.show', compact('sale'));
    }

    public function invoice(Sale $sale)
    {
        $sale->load(['user', 'customer', 'saleItems.product.category']);
        return view('sales.invoice', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        
        try {
            // Restore stock
            foreach ($sale->saleItems as $item) {
                $item->product->increment('stock', $item->quantity);
            }
            
            // Delete associated warranties
            foreach ($sale->saleItems as $item) {
                if ($item->warranty) {
                    $item->warranty->delete();
                }
            }
            
            // Update customer balance if needed
            if ($sale->due_amount > 0 && $sale->customer_id) {
                $sale->customer->decrement('balance', $sale->due_amount);
            }
            
            $sale->delete();
            
            DB::commit();
            
            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sales.index')->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }
}

