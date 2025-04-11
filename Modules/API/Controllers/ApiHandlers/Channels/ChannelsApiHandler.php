<?php

namespace Modules\API\Controllers\ApiHandlers\Channels;

use App\Repositories\ChannelRepository;
use Google\Service\DriveActivity\Edit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use Modules\API\BaseController;
use Modules\API\Channels\Models\Transformers\ChannelTransformer;
use Modules\API\Channels\Requests\AddChannelRequest;
use Modules\API\Channels\Requests\EditChannelRequest;
use Modules\HotelContentRepository\Models\Transformers\CustomFractalSerializer;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ChannelsApiHandler extends BaseController
{
    private Manager $fractal;

    /**
     * @param ChannelRepository $channelRepository
     */
    public function __construct(private readonly ChannelRepository $channelRepository)
    {
        $this->fractal = app(Manager::class);
        $this->fractal->setSerializer(app(CustomFractalSerializer::class));
    }

    /**
     * @param Collection $channelCollection
     * @return array
     */
    private function transformCollection(Collection $channelCollection): array
    {
        $resource = app(FractalCollection::class, ['data' => $channelCollection, 'transformer' => app(ChannelTransformer::class)]);
        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * @param Model $channel
     * @return array
     */
    private function transformItem(Model $channel): array
    {
        $resource = app(FractalItem::class, ['data' => $channel, 'transformer' => app(ChannelTransformer::class)]);
        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * @param AddChannelRequest $request
     * @return JsonResponse
     */
    public function add(AddChannelRequest $request): JsonResponse
    {
        $newChannel = $this->channelRepository->create($request->all());
        $transformedData = $this->transformItem($newChannel);

        return $this->sendResponse(
            $transformedData,
            'Channel created successfully'
        );
    }

    /**
     * @param int $channelId
     * @return JsonResponse
     */
    public function delete(int $channelId): JsonResponse // Assuming ID comes from route
    {
        $deleted = $this->channelRepository->delete($channelId);
        if (! $deleted) {
            return $this->sendError('Channel not found or could not be deleted', ResponseAlias::HTTP_NOT_FOUND);
        }
        return $this->sendResponse([], 'Channel deleted successfully', ResponseAlias::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function all(Request $request): JsonResponse
    {
        $channels = $this->channelRepository->all();
        $transformedData = $this->transformCollection($channels);

        return $this->sendResponse($transformedData, 'Channels retrieved successfully');
    }

    /**
     * @param int $channelId
     * @return JsonResponse
     */
    public function get(int $channelId): JsonResponse // Assuming ID comes from route
    {
        try {
            $channel = $this->channelRepository->findOrFail($channelId);
            $transformedData = $this->transformItem($channel);
            return $this->sendResponse($transformedData, 'Channel retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Channel not found', ResponseAlias::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param EditChannelRequest $request
     * @param int $channelId
     * @return JsonResponse
     */
    // Assuming an update method might look like this:
    public function edit(EditChannelRequest $request, int $channelId): JsonResponse // Assuming ID comes from route
    {
        try {
            $data = $request->all();
            $data['id'] = $channelId;
            $updatedChannel = $this->channelRepository->update($data);
            $transformedData = $this->transformItem($updatedChannel);
            return $this->sendResponse($transformedData, 'Channel updated successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendError('Channel not found', ResponseAlias::HTTP_NOT_FOUND);
        }
    }
}
