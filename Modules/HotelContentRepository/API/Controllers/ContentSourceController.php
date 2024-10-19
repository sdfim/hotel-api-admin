<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\API\Requests\ContentSourceRequest;
use Modules\API\BaseController;

class ContentSourceController extends BaseController
{
    public function index()
    {
        $contentSources = ContentSource::all();
        return $this->sendResponse($contentSources->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(ContentSourceRequest $request)
    {
        $contentSource = ContentSource::create($request->validated());
        return $this->sendResponse($contentSource->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $contentSource = ContentSource::findOrFail($id);
        return $this->sendResponse($contentSource->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(ContentSourceRequest $request, $id)
    {
        $contentSource = ContentSource::findOrFail($id);
        $contentSource->update($request->validated());
        return $this->sendResponse($contentSource->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $contentSource = ContentSource::findOrFail($id);
        $contentSource->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
