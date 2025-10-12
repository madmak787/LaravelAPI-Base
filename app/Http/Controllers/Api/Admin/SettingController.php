<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * GET /api/admin/settings
     * Returns key=>value map (optionally filtered by reference_id).
     */
    public function index(Request $request)
    {
        $referenceId = $request->query('reference_id'); // optional
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

    /**
     * POST /api/admin/settings
     * Accepts arbitrary key/value pairs (FormData or JSON).
     * If a field is an uploaded file, it will be stored and path saved.
     * Optional: pass reference_id (applies to all keys in this payload).
     */
    public function upsert(Request $request)
    {
        $referenceId = $request->input('reference_id'); // optional; may be null

        // We will iterate over all fields except control/meta fields
        $payload = $request->except(['_token', '_method']);

        foreach ($payload as $key => $value) {
            if ($key === 'reference_id') {
                continue; // handled above
            }

            // If this key is a file upload, handle it
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $path = $file->store('settings', 'public'); // e.g. storage/app/public/settings/...
                $value = $path;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value, 'reference_id' => $referenceId]
            );
        }

        // Invalidate both global and scoped caches since we don't know which changed
        Cache::forget('settings:global');
        if ($referenceId !== null && $referenceId !== '') {
            Cache::forget('settings:'.$referenceId);
        }

        return response()->json(['message' => 'Settings saved']);
    }
}
