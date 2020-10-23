<?php

namespace mangaslib\db;


use mangaslib\utilities\SeoHelper;

class Library {

    private $mysqli;

    public static function getDbConfig(){
        return dirname(dirname(dirname(__DIR__))) . '/db.ini';
    }

    function __construct() {
        $file = Library::getDbConfig();
        $ini = parse_ini_file($file, true);
        $this->open($ini);
    }

    public static function testConnection($uri, $username, $password, $dbName, $port) {
        try {
            $connection = @new \mysqli($uri, $username, $password, $dbName, intval($port));
            if ($connection->connect_errno) {
                return false;
            }

            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    private function open($connectionStrings) {
        if ($this->mysqli)
            return;
        
        $default = $connectionStrings['default'];
        $port = 3306;
        if (array_key_exists('port', $default)) {
            $port = intval($default['port']);
        }
        $this->mysqli = new \mysqli($default['uri'], $default['username'], $default['password'], $default['dbname'], $port);

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

    public function getAllSeries() {
        $query = 'SELECT * FROM mangas_series ORDER BY title;';
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $items = $result->fetch_all(MYSQLI_ASSOC);
        // TODO: call prepareSeries for each items
        $result->free();
        return $items;
    }

    // TODO: do we still need this?
    private function prepareSeries($series) {
        if ($series['genres']) $series['genres'] = preg_split( "/[ ,;]/", $series['genres'], -1, PREG_SPLIT_NO_EMPTY);
        if ($series['themes']) $series['themes'] = preg_split( "/[ ,;]/", $series['themes'], -1, PREG_SPLIT_NO_EMPTY);
        if ($series['alternate_titles']) $series['alternate_titles'] = json_decode($series['alternate_titles'], true);
        return $series;
    }

    // TODO: probably need more fields like short-name
    public function addSeries($title) {
        $titleEscaped = $this->mysqli->real_escape_string($title);
        $query = "INSERT INTO mangas_series (title) VALUES ('$titleEscaped')";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $id = $this->mysqli->insert_id;
        return [
            'id' => $id,
            'title' => $title,
            'library_status' => 0,
            'uri' => "/show-page/$id/" . SeoHelper::normalizeTitle($title)
        ];
    }

    public function deleteSeries($id) {
        $query = "DELETE FROM mangas_series WHERE id=$id LIMIT 1;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
    }

    public function getSeries($id) {
        $query = "SELECT * FROM mangas_series WHERE id=$id;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $items = $result->fetch_assoc();
        $result->free();
        return $items;
    }

    // TODO: probably handle an object and only update the present values.
    public function updateSeries($id, $seriesModel) {
        if (!is_array($seriesModel) || count($seriesModel) == 0) {
            return 0;
        }

        array_walk($seriesModel, function(&$value, $key) {
            if (is_string($value)) {
                $value = $this->mysqli->real_escape_string($value);
            }
            $value="$key='$value'";
        });
        $updateString = implode(', ', $seriesModel);
        $sql = "UPDATE mangas_series
                SET $updateString
                WHERE id=$id LIMIT 1;";
        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error, $sql);
        return 1;
    }

    public function findSeriesById($id) {
        $query = "SELECT * FROM mangas_series WHERE id = $id;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $items = $result->fetch_assoc();
        $result->free();
        return $this->prepareSeries($items);
    }

    public function getAllVolumes($seriesId, $ordering = "DESC") {
        $query = "SELECT * FROM mangas_volume WHERE title_id = $seriesId ORDER BY lang, volume $ordering;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $items;
    }

    public function AddVolume($id, $isbn, $volume, $lang) {
        $sql = "INSERT INTO mangas_volume (isbn, lang, volume, title_id) VALUES('$isbn','$lang', $volume , $id);";
        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error, $sql);
        //$result->free(); <----- crashes for some mysterious reason
    }

    public function deleteVolume($isbn) {
        $sql = "DELETE FROM mangas_volume WHERE isbn='$isbn' LIMIT 1;";
        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error, $sql);
    }

    public function updateVolume($isbn, $isbnNew, $volume, $lang) {
        $sql = "UPDATE mangas_volume
                SET isbn = '$isbnNew',
                    lang = '$lang',
                    volume = $volume
                WHERE isbn='$isbn' LIMIT 1;";
        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error, $sql);
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
        $result = $this->mysqli->query($sql) or $this->throwException($this->mysqli->error, $sql);
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

        $this->mysqli->query($sql) or $this->throwException($this->mysqli->error, $sql);
    }

    public function getScrapperData($scrapperId, $seriesId) {
        $sql = "SELECT * FROM mangas_scrapper WHERE id='$seriesId' AND scrapper_id='$scrapperId';";
        $result = $this->mysqli->query($sql) or $this->throwException($this->mysqli->error, $sql);
        $item = $result->fetch_assoc();
        return $item;
    }

    public function findUser($usernameOrEmail, $password) {
        $cond = "email='$usernameOrEmail'";
        if (strrpos($usernameOrEmail, "@") === FALSE) {
            $cond = "username='$usernameOrEmail'";
        }
        $query = "SELECT username, email, first_name, last_name, rolw FROM mangas_users WHERE $cond AND password='$password';";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $item = $result->fetch_assoc();
        $result->free();
        return $item;
    }

    public function addNewUser($username, $mail, $password, $role) {
        $query = "INSERT INTO mangas_users (username, password, email, rolw, first_name, last_name)
                  VALUES ('$username', '$password', '$mail', $role, '', '');";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        return true;
    }

    public function getUserCount() {
        return $this->count('email', 'mangas_users');
    }

    public function getSeriesCount() {
        return $this->count('id', 'mangas_series');
    }

    public function getVolumeCount() {
        return $this->count('isbn', 'mangas_volume');
    }

    public function getSeriesCompletedCount() {
        return $this->count('id', 'mangas_series', 'library_status=1');
    }

    public function isSeriesCompleted($titleId) {
        return $this->count('id', 'mangas_series', "library_status=1 AND id=$titleId");
    }

    public function getLatestVolumes($count = 5) {
        error_log("$count");
        $query = "SELECT * FROM mangas_volume ORDER BY created_date DESC LIMIT $count;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $items = $result->fetch_all(MYSQLI_ASSOC);

        $series = $this->getAllSeries();
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
        $titles = $this->getAllSeries();
        $items = [];
        foreach ($titles as $titleItem) {
            $titleId = $titleItem['id'];
            $isCompleted = intval($titleItem['library_status']);
            if (!$isCompleted) {
                $items[] = [
                    'id' => $titleId,
                    'missing' => $this->getMissingMangasForSeriesExt($titleId),
                    'data' => $titleItem
                ];
            }
        }

        return $items;
    }

    private function getMissingMangasForSeriesExt($titleId, $totalVolumes) {
        $query = "SELECT volume FROM mangas_volume WHERE title_id=$titleId ORDER BY volume;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
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
        if ($totalVolumes == 0 || $counter < $totalVolumes) {
            $missingVolumes[$counter] = true;
        }

        return array_keys($missingVolumes);
    }

    public function getMissingMangasForSeries($titleId) {
        $series = $this->getSeries($titleId);
        if (intval($series['library_status']) > 0) {
            return null;
        }

        return $this->getMissingMangasForSeriesExt($titleId, intval($series['volumes']));
    }

    private function count($field, $table, $where = '1') {
        $query = "SELECT COUNT($field) as total FROM $table WHERE $where;";
        $result = $this->mysqli->query($query) or $this->throwException($this->mysqli->error, $query);
        $item = $result->fetch_assoc();
        return $item['total'];
    }

    private function throwException($message, $query) {
        error_log("QUERY-> $query\n\n$message");
        throw new \Exception($message);
    }
}