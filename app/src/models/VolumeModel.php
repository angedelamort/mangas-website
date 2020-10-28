<?php

namespace mangaslib\models;

class VolumeModel extends BaseModel {
    public $isbn;
    public $lang;
    public $volume;
    const volume_type = "int";
    public $title_id; // foreign key
    const title_type = "int";
    public $created_date;

    private $series = null;

    /**
     * @param int $seriesId
     * @param string $ordering
     * @return array<VolumeModel>
     */
    public static function all($seriesId = null, $ordering = "DESC") {
        $cond = "1";
        if (intval($seriesId) > 0) {
            $cond = "title_id=$seriesId";
        }
        $query = "SELECT * FROM mangas_volume WHERE $cond ORDER BY lang, volume $ordering;";
        $result = self::query($query);
        $items = [];
        /** @var VolumeModel $item */
        while ($item = $result->fetch_object(VolumeModel::class)) {
            $items[] = $item;
        }
        return $items;
    }

    public static function find($isbn) {
        $query = "SELECT * FROM mangas_volume WHERE isbn='$isbn';";
        $result = self::query($query);
        /** @var VolumeModel $item */
        return $result->fetch_object(VolumeModel::class);
    }

    public static function remove($isbn) {
        $sql = "DELETE FROM mangas_volume WHERE isbn='$isbn' LIMIT 1;";
        return self::query($sql);
    }

    /**
     * @param VolumeModel $volume
     * @return object|\stdClass
     * @throws \ReflectionException
     */
    public static function add(VolumeModel $volume) {
        self::insert($volume, 'mangas_volume', ['created_date']);
        return VolumeModel::find($volume->isbn);
    }

    /**
     * @param VolumeModel $model
     * @param string $newKey
     * @return object|\stdClass
     * @throws \ReflectionException
     */
    public static function save(VolumeModel $model, string $newKey = null) {
        if ($newKey === null){
            $newKey = $model->isbn;
        }
        self::update($model, 'mangas_volume', "isbn='$newKey'");
        return VolumeModel::find($model->isbn);
    }

    public function series() {
        if ($this->series == null) {
            $this->series = SeriesModel::find($this->title_id);
        }

        return $this->series;
    }

    /**
     * @param int $seriesId
     * @return int
     */
    public static function size($seriesId = null) {
        $cond = (is_string($seriesId) && strlen($seriesId) > 0) ? $cond = "title_id=$seriesId" : "1";
        return self::count('isbn', 'mangas_volume', $cond);
    }

    /**
     * @param int $count
     * @return array<VolumeModel>
     */
    public static function latest($count = 5) {
        $query = "SELECT * FROM mangas_volume ORDER BY created_date DESC LIMIT $count;";
        $result = self::query($query);
        $items = [];
        /** @var VolumeModel $item */
        while ($item = $result->fetch_object(VolumeModel::class)) {
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @param int $seriesId
     * @param int $totalSeriesVolumes
     * @return array<int>
     */
    public static function missing($seriesId = null, $totalSeriesVolumes = 0) {
        $cond = "1";
        if (is_string($seriesId) && strlen($seriesId) > 0) {
            $cond = "title_id=$seriesId";
        }
        $query = "SELECT volume FROM mangas_volume WHERE $cond ORDER BY volume;";
        $result = self::query($query);
        $volumes = $result->fetch_all(MYSQLI_ASSOC);
        $missingVolumes = [];
        $counter = 1;
        foreach ($volumes as $volume) {
            $volumeId = $volume['volume'];
            while ($counter < $volumeId) {
                $missingVolumes[$counter] = true;
                $counter += 1;
            }
            if ($counter == $volumeId) // only increment when the same number : when 2 different language fir same volume
                $counter += 1;
        }

        // add item if new one is missing.
        if ($totalSeriesVolumes == 0 || $counter < $totalSeriesVolumes) {
            $missingVolumes[$counter] = true;
        }

        return array_keys($missingVolumes);
    }
}