<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\SetPlatformLogoRequest;
use App\Http\Requests\Store\SetStoreImageRequest;
use App\Http\Requests\Store\StoreImageRequest;
use App\Http\Resources\StoreResource;
use App\Models\Media;
use App\Models\PlatformLogo;
use App\Models\Store;
use App\Services\MediaService;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoreImageController extends Controller
{
    public function __construct(private readonly StoreService $stores) {}

    public function upload(StoreImageRequest $request, string $field): JsonResponse
    {
        $store = $this->stores->saveImage($this->store($request), $request->file('image'), $field);

        return response()->json(['store' => new StoreResource($store)]);
    }

    /**
     * Elegir el logo o la portada de la biblioteca, sin volver a subir el archivo.
     */
    public function set(SetStoreImageRequest $request, string $field): JsonResponse
    {
        $store = $this->store($request);
        $mediaId = (int) $request->validated()['media_id'];

        if (! Media::where('store_id', $store->id)->whereKey($mediaId)->exists()) {
            throw new AccessDeniedHttpException('The image does not belong to the store.');
        }

        return response()->json([
            'store' => new StoreResource($this->stores->setImage($store, $mediaId, $field)),
        ]);
    }

    /**
     * Tomar como logo uno de los que dejó el superadmin.
     *
     * La imagen se copia a la biblioteca de la tienda: de ahí en más es suya y
     * no depende de que el logo siga en la galería.
     */
    public function platform(SetPlatformLogoRequest $request, MediaService $media): JsonResponse
    {
        $store = $this->store($request);
        $logo = PlatformLogo::findOrFail($request->validated()['platform_logo_id']);

        $copy = $media->copyFrom($store, $logo);

        return response()->json([
            'store' => new StoreResource($this->stores->setImage($store, $copy->id, 'logo')),
        ]);
    }

    public function destroy(Request $request, string $field): JsonResponse
    {
        $store = $this->stores->deleteImage($this->store($request), $field);

        return response()->json(['store' => new StoreResource($store)]);
    }

    private function store(Request $request): Store
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        return $store;
    }
}
