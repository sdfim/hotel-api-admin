<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\Actions\ContentSource\AddContentSource;
use Modules\HotelContentRepository\Actions\ContentSource\DeleteContentSource;
use Modules\HotelContentRepository\Actions\ContentSource\EditContentSource;
use Modules\HotelContentRepository\API\Requests\ContentSourceRequest;
use Modules\HotelContentRepository\Models\ContentSource;

class ContentSourceController extends BaseController
{
    public function __construct(
        protected AddContentSource $addContentSource,
        protected EditContentSource $editContentSource,
        protected DeleteContentSource $deleteContentSource
    ) {}

    public function index()
    {
        $query = ContentSource::query();
        $query = $this->filter($query, ContentSource::class);
        $contentSources = $query->get();

        return $this->sendResponse($contentSources->toArray(), 'index success');
    }

    public function store(ContentSourceRequest $request)
    {
        $contentSource = $this->addContentSource->handle($request);

        return $this->sendResponse($contentSource->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $contentSource = ContentSource::findOrFail($id);

        return $this->sendResponse($contentSource->toArray(), 'show success');
    }

    public function update(ContentSourceRequest $request, $id)
    {
        $contentSource = ContentSource::findOrFail($id);
        $contentSource = $this->editContentSource->handle($contentSource, $request);

        return $this->sendResponse($contentSource->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $contentSource = ContentSource::findOrFail($id);
        $this->deleteContentSource->handle($contentSource);

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
