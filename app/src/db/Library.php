<?php

namespace mangaslib\db;

// TODO: probably not a good dependency
use mangaslib\extensions\LinkTwigExtension;
use mangaslib\scrappers\AnilistScrapper;



class Library {

    private $priorityString = "'ann', 'anilist'";
    private $mysqli;

    function __construct() {
        $file = dirname(dirname(dirname(__DIR__))) . '/db.ini';
        $ini = parse_ini_file($file, true);
        $this->open($ini);
    }

    private function open($connectionStrings) {
        if ($this->mysqli)
            return;
        
        $default = $connectionStrings['default'];
        $this->mysqli = new \mysqli($default['uri'], $default['username'], $default['password'], $default['dbname']);

        if ($this->mysqli->connect_errno) {
            throw new \Exception($this->mysqli->error);
        }

        $this->mysqli->query("SET NAMES 'utf8");
        $this->mysqli->query("SET CHARACTER SET utf8");
    }

    public function close() {
        if ($this->mysqli) {
            $this->mysqli->close();
            $this->mysqli = null;
        }
    }

    public function getAllSeries($addExtraInfo = false) {
        $query = 'SELECT * FROM mangas_title ORDER BY title;';
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
        $items = $result->fetch_all(MYSQLI_ASSOC);

        if ($addExtraInfo) {
            $query = "SELECT t.id, t.title, s.thumbnail, t.is_complete, s.scrapper_id 
                        FROM mangas_title t 
                        LEFT OUTER JOIN mangas_scrapper s ON t.id = s.id
                        ORDER BY t.title ASC, FIELD(s.scrapper_id, $this->priorityString);";
            $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
            $items = $result->fetch_all(MYSQLI_ASSOC);

            // todo: make a more generic function that can be used.
            function add($array, $index, $scrapperId) {
                if ($scrapperId) {
                    // TODO: move priority else where and make a better merge.
                    $priorityTable = [
                        'ann' => 1,
                        'anilist' => 2
                    ];
                    $priority = $priorityTable[$scrapperId];
                    $newItem = ['i' => $index, 'p' => $priority];

                    if (count($array) == 0){
                        $array[] = $newItem;
                    } else {
                        if ($priority > $array[0]['p']) {
                            array_unshift($array , $newItem);
                        } else {
                            $array[] = $newItem;
                        }
                    }
                }

                return $array;
            }

            // TODO: with the order by field -> just need to override the fields properly.
            // @see populateExtraDataToSeries()

            // find duplicates
            $dupplicates = [];
            for ($i = 0; $i < count($items); $i++) {
                $curr = $items[$i];
                $id = $curr['id'];
                $dupplicates[$id] = add(array_key_exists($id, $dupplicates) ? $dupplicates[$id] : [], $i, $curr['scrapper_id']);
            }
            // and remove them
            foreach ($dupplicates as $dupItems) {
                for ($i = 1; $i < count($dupItems); $i++) {
                    $index = $dupItems[$i]['i'];
                    unset($items[$index]);
                }
            }

            $result->free();
            return $items;
        }
        else {
            $query = 'SELECT * FROM mangas_title ORDER BY title;';
            $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
            $items = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
            return $items;
        }
    }

    public function addSeries($title) {
        $titleEscaped = $this->mysqli->real_escape_string($title);
        $query = "INSERT INTO mangas_title (title) VALUES ('$titleEscaped')";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
        $id = $this->mysqli->insert_id;
        $twigExt = new LinkTwigExtension();
        return [
            'id' => $id,
            'title' => $title,
            'is_complete' => 0,
            'uri' => "/show-page/$id/" . $twigExt->formatValue($title)
        ];
    }

    public function deleteSeries($id) {
        $query = "DELETE FROM mangas_title WHERE id=$id LIMIT 1;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
    }

    public function UpdateSeries($id, $title) {
        $sql = "UPDATE mangas_title
                SET title = '$title'
                WHERE id=$id LIMIT 1;";
        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error);
    }

    public function findSeriesById($id) {
        $query = "SELECT * FROM mangas_title WHERE id = $id;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
        $items = $result->fetch_assoc();
        $result->free();
        return $items;
    }

    public function getAllVolumes($seriesId, $ordering = "DESC") {
        $query = "SELECT * FROM mangas_info WHERE title_id = $seriesId ORDER BY lang, volume $ordering;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $items;
    }

    public function AddVolume($id, $isbn, $volume, $lang) {
        $sql = "INSERT INTO mangas_info (isbn, lang, volume, title_id) VALUES('$isbn','$lang', $volume , $id);";
        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error);
        //$result->free(); <----- crashes for some mysterious reason
    }

    public function deleteVolume($isbn) {
        $sql = "DELETE FROM mangas_info WHERE isbn='$isbn' LIMIT 1;";
        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error);
    }

    public function updateVolume($isbn, $isbnNew, $volume, $lang) {
        $sql = "UPDATE mangas_info
                SET isbn = '$isbnNew',
                    lang = '$lang',
                    volume = $volume
                WHERE isbn='$isbn' LIMIT 1;";
        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error);
    }

    public function addOrUpdateToScrapper($item) {
        // TODO: Ugly hack for now -> use real mysql parameters and SunOrm!
        foreach ($item as &$value) {
            if (is_string($value)) {
                $value = $this->mysqli->real_escape_string($value);
            }
        }

        $updateCondition = "s.id = '$item[id]' AND s.scrapper_id = '$item[scrapper_id]'";
        $sql = "SELECT EXISTS(SELECT 1 FROM mangas_scrapper s WHERE $updateCondition);";
        $result = $this->mysqli->query($sql) or $this->throwException($this->mysqli->error);
        $row = mysqli_fetch_array($result);

        if (intval($row[0]) === 1) {
            $sql = "UPDATE mangas_scrapper s
                    SET id='$item[id]', scrapper_id='$item[scrapper_id]', genres='$item[genres]', themes='$item[themes]', 
                        description='$item[description]', comment='$item[comment]', rating='$item[rating]', thumbnail='$item[thumbnail]', 
                        scrapper_mapping='$item[scrapper_mapping]'
                    WHERE $updateCondition
                    LIMIT 1;";
        } else {
            $sql = "INSERT INTO mangas_scrapper (id, scrapper_id, genres, themes, description, comment, rating, thumbnail, scrapper_mapping) 
                    VALUES ('$item[id]', '$item[scrapper_id]', '$item[genres]', '$item[themes]', 
                            '$item[description]', '$item[comment]', '$item[rating]', '$item[thumbnail]', '$item[scrapper_mapping]');";
        }

        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error);                
    }

    public function populateExtraDataToSeries($series) {
        $query = "SELECT * FROM mangas_scrapper s WHERE id=$series[id] ORDER BY FIELD(s.scrapper_id, $this->priorityString);";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
        $items = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($items as $item) {
            if (strlen($item['genres']) > 0) $series['genres'] = explode(',', $item['genres']);
            if (strlen($item['themes']) > 0) $series['themes'] = explode(',', $item['themes']);
            if (strlen($item['description']) > 0) $series['description'] = $item['description'];
            if (strlen($item['thumbnail']) > 0) $series['thumbnail'] = $item['thumbnail'];
            if (strlen($item['thumbnail']) > 0) $series['cover'] = $item['thumbnail'];

            switch ($item['scrapper_id']) {
                case AnilistScrapper::ID:
                    AnilistScrapper::AddExtraData($series, $item['comment']);
                    break;
                default:
                    break;
            }
        }

        return $series;
    }

    public function findUser($usernameOrEmail, $password) {
        $cond = "email='$usernameOrEmail'";
        if (strrpos($usernameOrEmail, "@") === FALSE) {
            $cond = "username='$usernameOrEmail'";
        }
        $query = "SELECT username, email, first_name, last_name, rolw FROM mangas_users WHERE $cond AND password='$password';";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
        $item = $result->fetch_assoc();
        $result->free();
        return $item;
    }

    public function getSeriesCount() {
        return $this->count('id', 'mangas_title');
    }

    public function getVolumeCount() {
        return $this->count('isbn', 'mangas_info');
    }

    public function getSeriesCompletedCount() {
        return $this->count('id', 'mangas_title', 'is_complete=1');
    }

    public function getLatestVolumes($count = 5) {
        error_log("$count");
        $query = "SELECT * FROM mangas_info ORDER BY created_date DESC LIMIT $count;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
        $items = $result->fetch_all(MYSQLI_ASSOC);

        $series = $this->getAllSeries(true);
        $map = [];
        foreach ($series as $s) {
            $map[$s['id']] = $s;
        }

        $latestVolumes = [];
        foreach ($items as $item) {
            $latestVolumes[] = [
                'date' => $item['created_date'],
                'isbn' => $item['isbn'],
                'volume' => $item['volume'],
                'series' => $map[$item['title_id']]
            ];
        }
        return $latestVolumes;
    }

    public function getMissingMangas() {
        $titles = $this->getAllSeries(true);
        $items = [];
        foreach ($titles as $titleItem) {
            $titleId = $titleItem['id'];
            $items[] = [
                'id' => $titleId,
                'missing' => $this->getMissingMangasForSeries($titleId),
                'data' => $titleItem
            ];
        }

        return $items;
    }

    private function getMissingMangasForSeries($titleId) {
        $query = "SELECT volume FROM mangas_info WHERE title_id=$titleId ORDER BY volume;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
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

        $missingVolumes[$counter] = true; // add last item
        return array_keys($missingVolumes);
    }

    private function count($field, $table, $where = '1') {
        $query = "SELECT COUNT($field) as total FROM $table WHERE $where;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error);
        $item = $result->fetch_assoc();
        return $item['total'];
    }

    private function throwException($message) {
        error_log($message);
        throw new \Exception($message);
    }
}