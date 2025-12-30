<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale)
    {
        $this->sale = $sale;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $shopSettings = \App\Models\Setting::getShopSettings();
        
        return $this->subject('Invoice ' . $this->sale->invoice_no . ' - ' . ($shopSettings['shop_name'] ?? 'Your Order'))
                    ->view('emails.invoice')
                    ->with([
                        'sale' => $this->sale,
                        'shopSettings' => $shopSettings,
                    ]);
    }
}
