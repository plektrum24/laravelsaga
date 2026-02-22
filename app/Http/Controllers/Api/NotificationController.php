<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\WebOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Register device for push notifications
     * POST /api/mobile/notifications/register-device
     */
    public function registerDevice(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
            'device_type' => 'required|in:ios,android',
            'device_id' => 'nullable|string',
        ]);

        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        // Create or update device token
        DB::table('push_notification_devices')->updateOrInsert(
            [
                'customer_id' => $customerId,
                'device_token' => $validated['device_token'],
            ],
            [
                'device_type' => $validated['device_type'],
                'device_id' => $validated['device_id'] ?? null,
                'last_used_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully'
        ]);
    }

    /**
     * Unregister device
     * DELETE /api/mobile/notifications/unregister-device
     */
    public function unregisterDevice(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
        ]);

        DB::table('push_notification_devices')
            ->where('device_token', $validated['device_token'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device unregistered successfully'
        ]);
    }

    /**
     * Get notification preferences
     * GET /api/mobile/notifications/preferences
     */
    public function getPreferences()
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer = Customer::findOrFail($customerId);
        $preferences = $customer->notification_preferences ?? [
            'order_updates' => true,
            'promotions' => true,
            'points_expiry' => true,
            'new_products' => false,
            'price_drops' => false,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'preferences' => $preferences,
                'customer_id' => $customerId,
            ]
        ]);
    }

    /**
     * Update notification preferences
     * PUT /api/mobile/notifications/preferences
     */
    public function updatePreferences(Request $request)
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $validated = $request->validate([
            'order_updates' => 'boolean',
            'promotions' => 'boolean',
            'points_expiry' => 'boolean',
            'new_products' => 'boolean',
            'price_drops' => 'boolean',
        ]);

        $customer = Customer::findOrFail($customerId);
        $preferences = $customer->notification_preferences ?? [];
        $preferences = array_merge($preferences, $validated);

        $customer->update([
            'notification_preferences' => $preferences,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'data' => [
                'preferences' => $preferences,
            ]
        ]);
    }

    /**
     * Get notification history
     * GET /api/mobile/notifications/history
     */
    public function history(Request $request)
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $notifications = DB::table('push_notifications')
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     * PUT /api/mobile/notifications/{id}/read
     */
    public function markAsRead($id)
    {
        DB::table('push_notifications')
            ->where('id', $id)
            ->where('customer_id', auth()->user()->customer_id ?? null)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Send order status notification (internal use)
     * This would be called by order management system
     */
    public function sendOrderNotification(WebOrder $order, $status, $message)
    {
        $customerId = $order->customer_id;

        if (!$customerId) {
            return false;
        }

        $customer = Customer::find($customerId);
        
        // Check if customer wants order updates
        $preferences = $customer->notification_preferences ?? [];
        if (!($preferences['order_updates'] ?? true)) {
            return false;
        }

        // Get customer's device tokens
        $devices = DB::table('push_notification_devices')
            ->where('customer_id', $customerId)
            ->get();

        foreach ($devices as $device) {
            // Send push notification via FCM
            $this->sendPushNotification(
                $device->device_token,
                $device->device_type,
                [
                    'title' => 'Order Update',
                    'body' => $message,
                    'data' => [
                        'type' => 'order_update',
                        'order_number' => $order->order_number,
                        'status' => $status,
                    ],
                ]
            );
        }

        // Log notification
        DB::table('push_notifications')->insert([
            'customer_id' => $customerId,
            'title' => 'Order Update',
            'message' => $message,
            'type' => 'order_update',
            'data' => json_encode([
                'order_number' => $order->order_number,
                'status' => $status,
            ]),
            'created_at' => now(),
        ]);

        return true;
    }

    /**
     * Send push notification via FCM
     * Internal method
     */
    private function sendPushNotification($deviceToken, $deviceType, $notification)
    {
        // Firebase Cloud Messaging API
        $fcmKey = config('services.firebase.server_key');

        if (!$fcmKey) {
            // FCM not configured, skip
            return;
        }

        $payload = [
            'to' => $deviceToken,
            'notification' => [
                'title' => $notification['title'],
                'body' => $notification['body'],
                'sound' => 'default',
            ],
            'data' => $notification['data'] ?? [],
        ];

        // Send to FCM
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'key=' . $fcmKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            return $response->successful();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send promotional notification (admin feature)
     * POST /api/admin/notifications/promotional
     */
    public function sendPromotional(Request $request)
    {
        // Admin-only endpoint
        // This would send promotional notifications to all users or segments

        $validated = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
            'customer_ids' => 'nullable|array', // Send to specific customers
            'send_to_all' => 'boolean',
        ]);

        $customerIds = $validated['customer_ids'] ?? [];

        if ($validated['send_to_all'] ?? false) {
            $customerIds = Customer::pluck('id')->toArray();
        }

        $sentCount = 0;

        foreach ($customerIds as $customerId) {
            $customer = Customer::find($customerId);
            
            // Check preferences
            $preferences = $customer->notification_preferences ?? [];
            if (!($preferences['promotions'] ?? true)) {
                continue;
            }

            // Get devices
            $devices = DB::table('push_notification_devices')
                ->where('customer_id', $customerId)
                ->get();

            foreach ($devices as $device) {
                $this->sendPushNotification(
                    $device->device_token,
                    $device->device_type,
                    [
                        'title' => $validated['title'],
                        'body' => $validated['message'],
                        'data' => [
                            'type' => 'promotional',
                        ],
                    ]
                );
            }

            // Log notification
            DB::table('push_notifications')->insert([
                'customer_id' => $customerId,
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => 'promotional',
                'created_at' => now(),
            ]);

            $sentCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Promotional notification sent',
            'data' => [
                'sent_count' => $sentCount,
            ]
        ]);
    }
}
