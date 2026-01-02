<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleContent extends Model
{
    use HasFactory;

    protected $table = 'oracle_contents';

    protected $connection;

    protected $fillable = [
        'room_classes',
        'rooms',
        'room_types',
        'code',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $cacheDB = config('database.connections.mysql_cache.database');
        $this->table = "$cacheDB.oracle_contents";
        $this->connection = config('database.active_connections.mysql_cache');
    }

    protected $casts = [
        'room_classes' => 'array',
        'rooms' => 'array',
        'room_types' => 'array',
    ];
}
