<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Los ajustes generales de la plataforma que edita el superadmin. Los logos
 * también viven en `settings`, pero tienen sus propios endpoints.
 */
class SettingController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['settings' => $this->settings()]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_trash_days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        Setting::updateOrCreate(
            ['key' => Setting::STORE_TRASH_DAYS],
            ['value' => (string) $data['store_trash_days']],
        );

        return response()->json(['settings' => $this->settings()]);
    }

    /**
     * @return array{store_trash_days: int|null}
     */
    private function settings(): array
    {
        $days = Setting::where('key', Setting::STORE_TRASH_DAYS)->value('value');

        return [
            'store_trash_days' => $days === null ? null : (int) $days,
        ];
    }
}
