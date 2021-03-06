<?php

namespace mangaslib\models;


class SeriesModel extends BaseModel {

    public $id;
    const id_schema = ['type'=>'int', 'readonly' => true];
    public $title;
    public $library_status;
    public $rating;
    const rating_schema = ['type'=>'float'];
    public $series_status;
    public $short_name;
    public $volumes;
    const volumes_schema = ['type'=>'int'];
    public $chapters;
    const chapters_schema = ['type'=>'int'];
    public $genres;
    public $themes;
    public $editors;
    public $authors;
    public $thumbnail;
    public $cover;
    public $banner;
    public $alternate_titles;
    public $synopsis;
    const synopsis_schema = ['editor'=>'textarea'];
    public $comments;
    const comments_schema = ['editor'=>'textarea'];

    public function getAlternateTitles() : array {
        return ($this->alternate_titles) ? json_decode($this->alternate_titles, true) : [];
    }

    public function getGenres() {
        return ($this->genres) ? preg_split( "/[ ,;]/", $this->genres, -1, PREG_SPLIT_NO_EMPTY) : [];
    }

    public function getThemes() {
        return ($this->themes) ? preg_split( "/[ ,;]/", $this->themes, -1, PREG_SPLIT_NO_EMPTY) : [];
    }

    protected static function tableName() : string {
        return "mangas_series";
    }

    /**
     * Get all series available in the database.
     * @note implement a paging system if needed.
     * @return array<SeriesModel>
     */
    public static function all() {
        return self::findAll('*', '1', 'title');
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
        return self::findAll('*', 'library_status=0', 'title');
    }

    /**
     * @param $id
     * @return SeriesModel
     */
    public static function find($id) {
        return self::findById($id);
    }

    /**
     * @param $item
     * @return SeriesModel
     * @throws \ReflectionException
     */
    public static function add($item){
        self::insert($item, 'mangas_series');
        return self::find(self::getLastInsertedId());
    }

    public static function save(SeriesModel $item) {
        self::update($item, 'mangas_series', "id=$item->id");
        return SeriesModel::find($item->id);
    }

    /**
     * @param array<int>|int $id
     * @return bool
     */
    public static function remove($id){
        if (is_numeric($id)) {
            $id = [$id];
        }
        $condition = 'id=' . join(" OR id=", $id);
        $query = "DELETE FROM mangas_series WHERE $condition;";
        self::query($query);
        return true;
    }

    public static function size($isCompleted = false) { // todo - maybe have some sort of flags
        return self::count('id', 'mangas_series', $isCompleted ? 'library_status=1' : '1');
    }

    public static function isSeriesCompleted($id) {
        return self::count('id', 'mangas_series', "library_status=1 AND id=$id");
    }
}