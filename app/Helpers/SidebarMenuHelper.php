<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class SidebarMenuHelper
{
    public static function getSidebarMenu()
    {
        $menus = config('menu');
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return collect($menus)
            ->filter(function ($menu) use ($user) {
                return self::userCanAccess($menu['permission'] ?? null, $user);
            })
            ->map(function ($menu) use ($user) {
                if (isset($menu['sub_menu'])) {
                    $menu['sub_menu'] = collect($menu['sub_menu'])
                        ->filter(function ($submenu) use ($user) {
                            return self::userCanAccess($submenu['permission'] ?? null, $user);
                        })
                        ->values()
                        ->toArray();
                }

                return $menu;
            })
            ->filter(function ($menu) {
                if (isset($menu['sub_menu']) && empty($menu['sub_menu'])) {
                    return false;
                }

                return true;
            })
            ->values()
            ->toArray();
    }

    private static function userCanAccess($permission, $user): bool
    {
        if ($permission === null || $permission === '' || $permission === []) {
            return true;
        }

        $permissions = is_array($permission) ? $permission : [$permission];

        return $user->canAny($permissions);
    }
}
