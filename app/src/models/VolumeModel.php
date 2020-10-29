<?php

namespace mangaslib\models;

class VolumeModel extends BaseModel {
    public $isbn;
    public $lang;
    public $volume;
    const volume_schema = ['type'=>'int'];
    public $title_id; // foreign key
    const title_schema = ['type'=>'int'];
    public $created_date;

    private $series = null;

    protected static function primaryKey() : string {
        return 'isbn';
    }

    protected static function tableName() : string {
        return "mangas_volume";
    }

    /**
     * @param int $seriesId
     * @param string $ordering
     * @return array<VolumeModel>
     */
    public static function all($seriesId = null, $ordering = "DESC") {
        $cond = (intval($seriesId) > 0) ? "title_id=$seriesId" : '1';
        return self::findAll('*', $cond, "lang, volume $ordering");
    }

    public static function find(string $isbn) : VolumeModel {
        return self::findById($isbn);
    }

    public static function remove($isbn) {
        return self::deleteById($isbn);
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
        return self::findAll('*', '1', "created_date DESC LIMIT $count;");
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