<?php

namespace mangaslib\models;

class VolumeModel {
    public $isbn;
    public $lang;
    public $volume;
    public $title_id; // foreign key
    public $created_date;

    private $series = null;

    /**
     * @param int $seriesId
     * @param string $ordering
     * @return array<VolumeModel>
     */
    public static function all($seriesId = null, $ordering = "DESC") {
        $helper = new DatabaseHelper();
        $cond = "1";
        if (intval($seriesId) > 0) {
            $cond = "title_id=$seriesId";
        }
        $query = "SELECT * FROM mangas_volume WHERE $cond ORDER BY lang, volume $ordering;";
        $result = $helper->query($query);
        $items = [];
        /** @var VolumeModel $item */
        while ($item = $result->fetch_object(VolumeModel::class)) {
            $items[] = $item;
        }
        return $items;
    }

    public static function find($isbn) {
        $helper = new DatabaseHelper();
        $query = "SELECT * FROM mangas_volume WHERE isbn='$isbn';";
        $result = $helper->query($query);
        /** @var VolumeModel $item */
        return $result->fetch_object(VolumeModel::class);
    }

    public static function delete($isbn) {
        $helper = new DatabaseHelper();
        $sql = "DELETE FROM mangas_volume WHERE isbn='$isbn' LIMIT 1;";
        return $helper->query($sql);
    }

    public static function add(VolumeModel $volume) {
        $helper = new DatabaseHelper();
        $sql = $helper->objectToInsert($volume,'mangas_volume', ['created_date']);
        $helper->query($sql);
        return VolumeModel::find($volume->isbn);
    }

    // TODO: should create a new Model and call update. We should initialize using the db and with the query params update if there.
    public static function update($isbn, $isbnNew, $volume, $lang) {
        $helper = new DatabaseHelper();
        $sql = "UPDATE mangas_volume SET isbn='$isbnNew', lang='$lang', volume=$volume WHERE isbn='$isbn' LIMIT 1;";
        $helper->query($sql);
        return VolumeModel::find($isbnNew);
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
    public static function count($seriesId = null) {
        $helper = new DatabaseHelper();
        $cond = "1";
        if (is_string($seriesId) && strlen($seriesId) > 0) {
            $cond = "title_id=$seriesId";
        }
        return $helper->count('isbn', 'mangas_volume', $cond);
    }

    /**
     * @param int $count
     * @return array<VolumeModel>
     */
    public static function latest($count = 5) {
        $helper = new DatabaseHelper();
        $query = "SELECT * FROM mangas_volume ORDER BY created_date DESC LIMIT $count;";
        $result = $helper->query($query);
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
        $helper = new DatabaseHelper();
        $cond = "1";
        if (is_string($seriesId) && strlen($seriesId) > 0) {
            $cond = "title_id=$seriesId";
        }
        $query = "SELECT volume FROM mangas_volume WHERE $cond ORDER BY volume;";
        $result = $helper->query($query);
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