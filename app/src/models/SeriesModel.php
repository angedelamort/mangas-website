<?php

namespace mangaslib\models;


class SeriesModel {
    public $id;
    public $title;
    public $library_status;
    public $rating;
    public $series_status;
    public $short_name;
    public $volumes;
    public $chapters;
    public $editors;
    public $authors;
    public $genres;
    public $synopsis;
    public $cover;
    public $banner;
    public $thumbnail;
    public $alternate_titles;
    public $themes;

    /**
     * Get all series available in the database.
     * @note implement a paging system if needed.
     * @return array<SeriesModel>
     */
    public static function all() {
        $helper = new DatabaseHelper();
        $query = 'SELECT * FROM mangas_series ORDER BY title;';
        $result = $helper->query($query);
        $items = [];
        /** @var SeriesModel $item */
        while ($item = $result->fetch_object(SeriesModel::class)) {
            $item->postProcess();
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @param string $ordering
     * @return array<VolumeModel>
     */
    public function volumes($ordering = "DESC") {
        return VolumeModel::all($this->id, $ordering);
    }

    public function missingVolumes() {
        if (intval($this->library_status) > 0) {
            return null;
        }

        return VolumeModel::missing($this->id, $this->volumes);
    }

    public static function incomplete() {
        $helper = new DatabaseHelper();
        $query = 'SELECT * FROM mangas_series WHERE library_status=0 ORDER BY title;';
        $result = $helper->query($query);
        $items = [];
        /** @var SeriesModel $item */
        while ($item = $result->fetch_object(SeriesModel::class)) {
            $item->postProcess();
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @param $id
     * @return SeriesModel
     */
    public static function find($id) {
        // TODO: have a map for temporary caching during session instead.
        $helper = new DatabaseHelper();
        $query = "SELECT * FROM mangas_series WHERE id = $id;";
        $result = $helper->query($query);
        /** @var SeriesModel $item */
        $item = $result->fetch_object(SeriesModel::class);
        $item->postProcess();
        return $item;
    }

    public static function add($item){
        $helper = new DatabaseHelper();
        $helper->query($helper->arrayToInsert('mangas_series', $item));

        return SeriesModel::find($helper->getLastInsertedId());
    }

    public static function update($item) {
        $helper = new DatabaseHelper();
        if (!is_array($item) || count($item) == 0) {
            return 0;
        }

        // remove id if exists
        if (!array_key_exists('id', $item)) {
            return 0;
        }
        $id = $item['id'];
        unset($item['id']);

        // TODO: refactor this
        if (array_key_exists('rating', $item) && !$item['rating']) {
            unset($item['rating']);
        }

        $helper->query($helper->arrayToUpdate('mangas_series', $item, "id=$id"));
        return SeriesModel::find($id);
    }

    public static function delete($id){
        return SeriesModel::deleteMany([$id]);
    }

    public static function deleteMany(array $ids){
        $helper = new DatabaseHelper();
        $condition = 'id=' . join(" OR id=", $ids);
        $query = "DELETE FROM mangas_series WHERE $condition;";
        $helper->query($query);
        return true;
    }

    public static function count($isCompleted = false) { // todo - maybe have some sort of flags
        $helper = new DatabaseHelper();

        if ($isCompleted)
            return $helper->count('id', 'mangas_series', 'library_status=1');

        return $helper->count('id', 'mangas_series');
    }

    public static function isSeriesCompleted($id) {
        $helper = new DatabaseHelper();
        return $helper->count('id', 'mangas_series', "library_status=1 AND id=$id");
    }

    private function postProcess() {
        if ($this->genres) $this->genres = preg_split( "/[ ,;]/", $this->genres, -1, PREG_SPLIT_NO_EMPTY);
        if ($this->themes) $this->themes = preg_split( "/[ ,;]/", $this->themes, -1, PREG_SPLIT_NO_EMPTY);
        if ($this->alternate_titles) $this->alternate_titles = json_decode($this->alternate_titles, true);
        return $this;
    }
}