<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class BaseWithPolicyController extends Controller
{
    /**
     * Model class for detect access
     */
    protected static string $model = Model::class;

    /**
     * Corresponds to the name of the resource type router for obtaining a model instance
     */
    protected static ?string $parameterName = null;

    public function __construct()
    {
        $this->middleware($this->getCanMiddleware('view'))->only(['index', 'show']);
        $this->middleware($this->getCanMiddleware('create'))->only(['create', 'store']);
        $this->middleware($this->getCanMiddleware('update'))->only(['edit', 'update']);
        $this->middleware($this->getCanMiddleware('delete'))->only(['destroy']);
    }

    /**
     * @throws \ReflectionException
     */
    private function getCanMiddleware(string $ability)
    {
        $model = static::$model;
        if (is_null(static::$parameterName)) {
            $parameterName = (new \ReflectionClass($model))->getShortName();
            $parameterName = strtolower($parameterName);
        } else {
            $parameterName = static::$parameterName;
        }

        return function ($request, $next) use ($ability, $model, $parameterName) {
            if ($id = $request->route($parameterName)) {
                $instance = $model::findOrFail($id);
            }

            Gate::authorize($ability, $instance ?? $model);

            return $next($request);
        };
    }
}
