<?php

namespace mangaslib\db;


class Library {

    public static function getDbConfig(){
        return dirname(dirname(dirname(__DIR__))) . '/db.ini';
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
}