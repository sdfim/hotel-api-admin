<?php

namespace App\Repositories;

use App\Models\GiataPlace;
use App\Models\Mapping;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class GeneralMappingRepository
{
    protected string $mainDB;

    protected string $cacheDB;

    public function __construct()
    {
        $this->mainDB = config('database.connections.mysql.database');
        $this->cacheDB = config('database.connections.mysql_cache.database');
    }

    /**
     * Получает идентификаторы поставщика по месту назначения Giata (city ID или city name).
     *
     * @param  MappingSuppliersEnum  $supplier  Поставщик.
     * @param  string  $input  ID города (число) или название города (строка).
     * @param  int  $limit  Ограничение результатов.
     * @param  int  $page  Номер страницы.
     * @param  array  $filters  Дополнительные фильтры (например, 'hotel_name').
     */
    public function getIdsByDestinationGiata(MappingSuppliersEnum $supplier, string $input, int $limit = 100, int $page = 1, array $filters = []): array
    {
        $resultsQuery = DB::table($this->cacheDB.'.properties')
            ->join($this->mainDB.'.mappings', $this->cacheDB.'.properties.code', '=', $this->mainDB.'.mappings.giata_id')
            ->where(is_numeric($input) ? 'city_id' : 'city', $input)
            ->where($this->mainDB.'.mappings.supplier', $supplier->value)
            ->select($this->cacheDB.'.properties.code as giata', $this->cacheDB.'.properties.name', $this->mainDB.'.mappings.supplier_id as '.$supplier->name);

        if (isset($filters['hotel_name'])) {
            $resultsQuery->where($this->cacheDB.'.properties.hotel_name', 'like', '%'.$filters['hotel_name'].'%');
        }

        $results = $resultsQuery->get();

        return $this->paginateAndFormat($results, $limit, $page, $supplier->name);
    }

    /**
     * Получает идентификаторы поставщика по месту назначения (city ID или city name).
     *
     * @param  MappingSuppliersEnum  $supplier  Поставщик.
     * @param  string  $input  ID города (число) или название города (строка).
     * @param  int  $limit  Ограничение результатов.
     * @param  int  $page  Номер страницы.
     * @param  array  $filters  Дополнительные фильтры (например, 'hotel_name').
     */
    public function getIdsByDestination(MappingSuppliersEnum $supplier, string $input, int $limit = 100, int $page = 1, array $filters = []): array
    {
        $resultsQuery = DB::table($this->cacheDB.'.properties')
            ->join($this->mainDB.'.mappings', $this->cacheDB.'.properties.code', '=', $this->mainDB.'.mappings.giata_id')
            ->where(is_numeric($input) ? 'city_id' : 'city', $input)
            ->where($this->mainDB.'.mappings.supplier', $supplier->value)
            ->select($this->cacheDB.'.properties.code as giata', $this->cacheDB.'.properties.name', $this->mainDB.'.mappings.supplier_id as '.$supplier->name);

        if (isset($filters['hotel_name'])) {
            $resultsQuery->where($this->cacheDB.'.properties.hotel_name', 'like', '%'.$filters['hotel_name'].'%');
        }

        $results = $resultsQuery->get();

        return $this->paginateAndFormat($results, $limit, $page, $supplier->name);
    }

    /**
     * Получает идентификаторы поставщика по Giata Place (помеченному месту).
     *
     * @param  MappingSuppliersEnum  $supplier  Поставщик.
     * @param  string  $place  Название Giata Place ('key').
     * @param  int  $limit  Ограничение результатов.
     * @param  int  $page  Номер страницы.
     */
    public function getIdsByGiataPlace(MappingSuppliersEnum $supplier, string $place, int $limit = 100, int $page = 1): array
    {
        // Находим tticodes для Giata Place
        $ttiCodes = GiataPlace::where('key', $place)->select('tticodes')->first()->tticodes ?? [];

        // Получаем мэппинг по giata_id и поставщику
        $results = Mapping::where('supplier', $supplier->value)
            ->whereIn('giata_id', $ttiCodes)
            ->select('giata_id', 'supplier_id') // Указываем явно, чтобы получить ['giata_id', 'supplier_id']
            ->get();

        return $this->paginateAndFormat($results, $limit, $page, $supplier->name);
    }

    /**
     * Получает идентификаторы поставщика по координатам (Bounding Box).
     *
     * @param  MappingSuppliersEnum  $supplier  Поставщик.
     * @param  array  $minMaxCoordinate  Массив с ключами: min_latitude, max_latitude, min_longitude, max_longitude.
     * @param  int  $limit  Ограничение результатов.
     * @param  int  $page  Номер страницы.
     * @param  array  $filters  Дополнительные фильтры (например, 'hotel_name').
     */
    public function getIdsByCoordinate(MappingSuppliersEnum $supplier, array $minMaxCoordinate, int $limit = 100, int $page = 1, array $filters = []): array
    {
        $resultsQuery = DB::table($this->cacheDB.'.properties')
            ->where($this->cacheDB.'.properties.latitude', '>', $minMaxCoordinate['min_latitude'])
            ->where($this->cacheDB.'.properties.latitude', '<', $minMaxCoordinate['max_latitude'])
            ->where($this->cacheDB.'.properties.longitude', '>', $minMaxCoordinate['min_longitude'])
            ->where($this->cacheDB.'.properties.longitude', '<', $minMaxCoordinate['max_longitude'])
            ->join($this->mainDB.'.mappings', $this->cacheDB.'.properties.code', '=', $this->mainDB.'.mappings.giata_id')
            ->where($this->mainDB.'.mappings.supplier', $supplier->value)
            ->select($this->cacheDB.'.properties.code as giata', $this->cacheDB.'.properties.name', $this->mainDB.'.mappings.supplier_id as '.$supplier->name);

        if (isset($filters['hotel_name'])) {
            $resultsQuery->where($this->cacheDB.'.properties.name', 'like', '%'.$filters['hotel_name'].'%');
        }

        $results = $resultsQuery->get();

        return $this->paginateAndFormat($results, $limit, $page, $supplier->name);
    }

    /**
     * Получает идентификаторы поставщика по массиву Giata IDs.
     *
     * @param  MappingSuppliersEnum  $supplier  Поставщик.
     * @param  array  $giataIds  Массив Giata ID.
     * @param  int  $limit  Ограничение результатов.
     * @param  int  $page  Номер страницы.
     */
    public function getIdsByGiataIds(MappingSuppliersEnum $supplier, array $giataIds, int $limit = 100, int $page = 1): array
    {
        $results = Mapping::where('supplier', $supplier->value)
            ->whereIn('giata_id', $giataIds)
            ->select('giata_id', 'supplier_id')
            ->get();

        return $this->paginateAndFormat($results, $limit, $page, $supplier->value);
    }

    /**
     * Обрабатывает коллекцию результатов, применяет пагинацию и форматирует результат.
     *
     * @param  Collection  $results  Коллекция результатов из базы данных.
     * @param  int  $limit  Максимальное количество результатов на странице.
     * @param  int  $page  Номер страницы.
     * @param  string  $supplierKey  Ключ поставщика для поля 'supplier_id' (например, 'hbsi').
     */
    protected function paginateAndFormat(Collection $results, int $limit, int $page, string $supplierKey): array
    {
        // Преобразуем коллекцию в массив с ключами по supplier_id
        $resultsArray = $results
            ->mapWithKeys(function ($value) use ($supplierKey) {
                // Предполагается, что объекты содержат поля giata, name и hbsi (или другой supplierKey)
                $data = [
                    'giata' => $value->giata ?? $value['giata_id'], // Поддержка DB::table и Eloquent
                    $supplierKey => $value->{$supplierKey} ?? $value['supplier_id'],
                ];

                // Добавляем 'name' только если оно существует (актуально для запросов с joined properties)
                if (isset($value->value)) {
                    $data['name'] = $value->value;
                }

                return [
                    $value->{$supplierKey} ?? $value['supplier_id'] => $data,
                ];
            })
            ->toArray();

        $totalResults = count($resultsArray);
        $totalPages = (int) ceil($totalResults / $limit);

        // Пагинация
        $offset = $page > 1 ? ($page - 1) * $limit : 0;
        $paginatedResults = array_slice($resultsArray, $offset, $limit);

        // Пересоздаем ассоциативный массив с ключом 'hbsi' (или другим supplierKey)
        $associativeArray = array_column($paginatedResults, null, $supplierKey);

        return [
            'data' => $associativeArray,
            'total_pages' => $totalPages,
        ];
    }
}
