<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function update(Request $request)
    {
        // Store settings logic here
        // You can use a settings table or config file
        
        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}



