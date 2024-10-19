<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

class BaseWithPolicyController extends Controller
{
    protected static string $model = Model::class;

    public function __construct()
    {
        $model = static::$model;
        $this->middleware("can:view,$model")->only(['index', 'show']);
        $this->middleware("can:create,$model")->only(['create', 'store']);
        $this->middleware("can:update,$model")->only(['edit', 'update']);
        $this->middleware("can:delete,$model")->only('destroy');
    }
}
