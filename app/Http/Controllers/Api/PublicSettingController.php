<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicSettingController extends Controller
{
    /**
     * GET /api/settings
     * Public read-only settings (global or scoped by reference_id).
     */
    public function show(Request $request)
    {
        $referenceId = $request->query('reference_id');
        $cacheKey = 'settings:'.($referenceId ?: 'global');

        $settings = Cache::rememberForever($cacheKey, function () use ($referenceId) {
            $query = Setting::query();
            if ($referenceId !== null && $referenceId !== '') {
                $query->where('reference_id', $referenceId);
            } else {
                $query->whereNull('reference_id');
            }

            return $query->get()->pluck('value', 'key');
        });

        return response()->json(['data' => $settings]);
    }
}
