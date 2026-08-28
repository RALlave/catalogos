<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WaitlistEntryResource;
use App\Models\Store;
use App\Models\WaitlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WaitlistController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Los pendientes primero: son los que hay que atender.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $entries = $this->userStore($request)
            ->waitlistEntries()
            ->with('product')
            ->when($request->boolean('pending'), fn ($query) => $query->whereNull('notified_at'))
            ->orderByRaw('notified_at IS NOT NULL')
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return WaitlistEntryResource::collection($entries);
    }

    /**
     * Marca o desmarca que ya se le avisó a esa persona.
     */
    public function notified(Request $request, WaitlistEntry $waitlistEntry): JsonResponse
    {
        Gate::authorize('update', $waitlistEntry);

        $waitlistEntry->update([
            'notified_at' => $request->boolean('notified', true) ? now() : null,
        ]);

        return response()->json(['entry' => new WaitlistEntryResource($waitlistEntry->load('product'))]);
    }

    /**
     * Borra el registro. Son datos personales de un tercero: se eliminan de
     * verdad, sin borrado suave.
     */
    public function destroy(WaitlistEntry $waitlistEntry): JsonResponse
    {
        Gate::authorize('delete', $waitlistEntry);

        $waitlistEntry->delete();

        return response()->json(null, 204);
    }

    private function userStore(Request $request): Store
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        return $store;
    }
}
