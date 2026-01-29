<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Display license generator page.
     */
    public function index()
    {
        return view('pages.admin.license.index');
    }

    /**
     * Generate license key.
     * Logic based on Node.js LicenseService.js
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|string',
            'duration_days' => 'required|integer|min:1',
        ]);

        $machineId = $validated['machine_id'];
        $durationDays = $validated['duration_days'];

        // Generate expiry date
        $expiryDate = now()->addDays($durationDays)->format('Y-m-d');

        // Create license data
        $licenseData = $machineId . '|' . $expiryDate;

        // Generate HMAC signature using secret key
        $secretKey = config('app.license_secret', 'SAGA_TOKO_OFFICIAL_SECURE_KEY_2024_XZ9');
        $signature = hash_hmac('sha256', $licenseData, $secretKey);

        // Format license key as SAGA-XXXX-XXXX-XXXX-XXXX
        $shortSig = strtoupper(substr($signature, 0, 16));
        $licenseKey = 'SAGA-' .
            substr($shortSig, 0, 4) . '-' .
            substr($shortSig, 4, 4) . '-' .
            substr($shortSig, 8, 4) . '-' .
            substr($shortSig, 12, 4);

        return response()->json([
            'success' => true,
            'data' => [
                'license_key' => $licenseKey,
                'machine_id' => $machineId,
                'expiry_date' => $expiryDate,
                'duration_days' => $durationDays,
            ]
        ]);
    }
}
