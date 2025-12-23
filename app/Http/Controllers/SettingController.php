<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        // Get all shop settings
        $shopName = Setting::get('shop_name', 'POS SYSTEM');
        $shopAddress = Setting::get('shop_address', '');
        $shopPhone = Setting::get('shop_phone', '');
        $shopEmail = Setting::get('shop_email', '');
        $footerMessage = Setting::get('footer_message', 'Thank you for your business!');
        $softwareCompanyName = Setting::get('software_company_name', '');
        $softwareCompanyWebsite = Setting::get('software_company_website', '');
        $softwareCompanyTagline = Setting::get('software_company_tagline', '');
        $showAdvertisement = Setting::get('show_advertisement', '0');

        return view('settings.index', compact('shopName', 'shopAddress', 'shopPhone', 'shopEmail', 'footerMessage', 'softwareCompanyName', 'softwareCompanyWebsite', 'softwareCompanyTagline', 'showAdvertisement'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_address' => 'nullable|string|max:500',
            'shop_phone' => 'nullable|string|max:50',
            'shop_email' => 'nullable|email|max:255',
            'footer_message' => 'nullable|string|max:500',
            'software_company_name' => 'nullable|string|max:255',
            'software_company_website' => 'nullable|url|max:255',
            'software_company_tagline' => 'nullable|string|max:500',
            'show_advertisement' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            // Update shop settings
            Setting::set('shop_name', $request->shop_name, 'text', 'shop', 'Shop/Business Name');
            Setting::set('shop_address', $request->shop_address ?? '', 'textarea', 'shop', 'Shop Address');
            Setting::set('shop_phone', $request->shop_phone ?? '', 'text', 'shop', 'Shop Phone Number');
            Setting::set('shop_email', $request->shop_email ?? '', 'email', 'shop', 'Shop Email Address');
            Setting::set('footer_message', $request->footer_message ?? 'Thank you for your business!', 'textarea', 'shop', 'Footer message on invoices/receipts');
            
            // Software company advertisement settings
            Setting::set('software_company_name', $request->software_company_name ?? '', 'text', 'advertisement', 'Software Company Name');
            Setting::set('software_company_website', $request->software_company_website ?? '', 'url', 'advertisement', 'Software Company Website');
            Setting::set('software_company_tagline', $request->software_company_tagline ?? '', 'textarea', 'advertisement', 'Software Company Tagline');
            Setting::set('show_advertisement', $request->has('show_advertisement') ? '1' : '0', 'boolean', 'advertisement', 'Show advertisement on invoices');

            // Clear cache to refresh settings
            Setting::clearCache();

            DB::commit();

            return redirect()->route('settings.index')->with('success', 'Shop settings updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating settings: ' . $e->getMessage())->withInput();
        }
    }
}




