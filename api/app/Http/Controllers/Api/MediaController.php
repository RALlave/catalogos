<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\CropMediaRequest;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Models\Store;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaController extends Controller
{
    private const PER_PAGE = 24;

    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $media = $this->userStore($request)
            ->media()
            ->with(['products:id,name', 'heroes:id,title,media_id'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->input('search').'%'))
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return MediaResource::collection($media);
    }

    public function store(StoreMediaRequest $request): JsonResponse
    {
        $media = $this->media->storeMany($this->userStore($request), $request->file('images'));

        return response()->json([
            'media' => MediaResource::collection($media->load(['products:id,name', 'heroes:id,title,media_id'])),
        ], 201);
    }

    public function show(Request $request, Media $media): JsonResponse
    {
        Gate::authorize('view', $media);

        return response()->json([
            'media' => new MediaResource($media->load(['products:id,name', 'heroes:id,title,media_id'])),
        ]);
    }

    public function update(UpdateMediaRequest $request, Media $media): JsonResponse
    {
        Gate::authorize('update', $media);

        $media = $this->media->update($media, $request->validated());

        return response()->json([
            'media' => new MediaResource($media->load(['products:id,name', 'heroes:id,title,media_id'])),
        ]);
    }

    public function crop(CropMediaRequest $request, Media $media): JsonResponse
    {
        Gate::authorize('update', $media);

        $media = $this->media->crop($media, $request->validated());

        return response()->json([
            'media' => new MediaResource($media->load(['products:id,name', 'heroes:id,title,media_id'])),
        ]);
    }

    public function destroy(Request $request, Media $media): JsonResponse
    {
        Gate::authorize('delete', $media);

        /* La copia de un logo de la plataforma la administra el sistema: entra
           al elegir el logo y se va al cambiarlo. */
        if ($media->from_platform) {
            throw new AccessDeniedHttpException('The image is managed by the platform.');
        }

        /* Una imagen en uso no se borra: primero hay que sacarla de sus
           productos, de sus heros, del logo o de la portada. */
        if ($media->isInUse()) {
            throw new AccessDeniedHttpException('The image is in use.');
        }

        $this->media->delete($media);

        return response()->json(['message' => __('Media deleted.')]);
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
