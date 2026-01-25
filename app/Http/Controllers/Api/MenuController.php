<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function getMenus(Request $request)
    {
        $user = $request->user();
        if (!$user->tenant) {
            return response()->json(['success' => false, 'message' => 'No tenant assigned'], 400);
        }

        $businessType = $user->tenant->business_type;
        $menus = [];

        // 1. Load Menu Config based on Business Type
        if ($businessType === 'retail') {
            $menus = require app_path('Modules/Retail/Config/menu.php');
        } elseif ($businessType === 'barber') {
            $menus = require app_path('Modules/Barber/Config/menu.php');
        }

        // 2. Filter Menus based on User Role (Simple Logic)
        // In real app, check Spatie Permissions here.
        // For now, we use the simple 'roles' array in config.

        $filteredMenus = [];
        $userRoles = $user->getRoleNames()->toArray();
        if (empty($userRoles))
            $userRoles[] = 'guest';

        foreach ($menus as $section) {
            $filteredItems = [];
            foreach ($section['items'] as $item) {
                // Check Visibility
                // Allow if 'all' is in config OR user has at least one matching role
                $allowedRoles = $item['roles'];
                $hasAccess = in_array('all', $allowedRoles) || !empty(array_intersect($userRoles, $allowedRoles));

                if ($hasAccess) {
                    $filteredItems[] = $item;
                }
            }

            if (!empty($filteredItems)) {
                $filteredMenus[] = [
                    'title' => $section['title'],
                    'items' => $filteredItems
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $filteredMenus
        ]);
    }
}
