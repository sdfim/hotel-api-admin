<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportMapperExpediaGiata extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $connection;

    protected $table = 'report_mapper_expedia_giata';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('database.active_connections.mysql_cache');
    }
}
